<?php
namespace BZIon\Form\Type;

use BZIon\Form\Transformer\PlayerTransformer;
use Player;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
    /**
     * Whether the user gave us usernames or IDs
     * @var boolean
     */
    private $listUsernames = false;

    /**
     * A player to always include
     * @var Player|null
     */
    private $include = null;

    /**
     * Whether more than 1 players can be provided
     * @var boolean
     */
    private $multiple = false;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['include'])) {
            $this->include = $options['include'];
        }

        if (isset($options['multiple'])) {
            $this->multiple = $options['multiple'];
        }

        $placeholder = ($this->multiple) ? 'brad, kierra, ...' : null;

        $transformer = new PlayerTransformer();

        $builder->add(
            $builder->create('players', 'text', array(
                'attr' => array(
                    'class'       => 'player-select',
                    'placeholder' => $placeholder,
                ),
                'label'    => ucfirst($builder->getName()) . ': ',
                'required' => false
            ))->addViewTransformer($transformer)
        );

        // True if the client provided the recipient usernames instead of IDs
        // (to support non-JS browsers)
        $builder->add('ListUsernames', 'hidden', array(
            'attr' => array(
                'class' => 'player-select-type',
            ),
            'data' => true,
        ));

        $builder->addEventListener(FormEvents::SUBMIT, array($this, 'onSubmit'));
    }

    /**
     * Render a list of comma-separated usernames for the user to see
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $data = $view->vars['value'];

        if ($data === null) {
            $data = $form->get('players')->getData();

            if ($data === null) {
                return;
            }
        }

        // Human-readable list to show in case the user has javascript disabled
        $usernames = $this->reverseTransform($data,
            function ($player) { return $player->getUsername(); }
        );
        if (is_array($usernames)) {
            $usernames = implode(', ', $usernames);
        }

        // Array that the javascript will parse to fill select2's list
        $json = json_encode($this->reverseTransform($data,
            function ($player) { return array(
                'id'       => $player->getId(),
                'username' => $player->getUsername()
            );
        }));

        $view->children['players']->vars['attr']['data-value'] = $json;
        $view->children['players']->vars['value'] = $usernames;

        // Reset listUsernames in case the user has turned javascript off since
        // the last request
        $view->children['ListUsernames']->vars['value'] = '1';
    }

    /**
     * Convert the vague array that the user gave us into meaningful models
     *
     * We use events instead of model/view transformers so that a proper error
     * message is show to the user when he enters the form
     *
     * @param  FormEvent $event
     * @return void
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $this->listUsernames = (bool) $data['ListUsernames'];
        $players = $data['players'];
        $form = $event->getForm()->get('players');

        if (trim($players) == '') {
            if ($this->multiple) {
                // The user didn't include any players, just set data to be an empty
                // array so that we do foreach() without any tricks
                $data = array();
            } else {
                $data = null;
            }
        } else {
            $data = $this->stringToModels($players, $form);
        }

        $event->setData($data);
    }

    /**
     * Convert a comma-separated string into an array of models
     * @param  string        $string
     * @param  FormInterface $form   A form to write errors into
     * @return Player[]
     */
    private function stringToModels($string, $form)
    {
        // Convert the comma-separated list of players the user gave us into an
        // array
        $players = explode(',', $string);

        // Remove all the whitespace and duplicate entries
        $players = array_map('trim', $players);
        $players = array_unique($players);

        $models = array();

        foreach ($players as $player) {
            try {
                $model = ($this->listUsernames)
                       ? $this->usernameToModel($player)
                       : $this->idToModel($player);

                if ($model) {
                    if (!$this->multiple) {
                        return $model;
                    }

                    if ($this->include && $model->getId() == $this->include->getId()) {
                        // The caller has explicitly asked to include this player,
                        // so we just ignore any entries the user gave us to prevent
                        // duplications
                        continue;
                    }

                    $models[] = $model;
                }
            } catch (InvalidNameException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        if ($this->include) {
            $models[] = $this->include;
        }

        return $models;
    }

    /**
     * Convert a username to a model
     *
     * Empty usernames are ignored
     *
     * @throws InvalidNameException
     * @param  string               $username The username
     * @return Player|null
     */
    private function usernameToModel($username)
    {
        if (empty($username)) {
            return;
        }

        $player = Player::getFromUsername($username);

        if (!\HTMLController::canSee($player)) {// Symfony auto-escapes $username
            throw new InvalidNameException("There is no player called $username");
        }

        return $player;
    }

    /**
     * Convert a player ID to a model
     *
     * @throws InvalidNameException
     * @param  int                  $id The player ID
     * @return Player
     */
    private function idToModel($id)
    {
        $id = (int) $id;
        $player = new Player($id);

        if (!\HTMLController::canSee($player)) {
            throw new InvalidNameException("There is no player with ID $id");
        }

        return $player;
    }

    /**
     * Converts an array of models into a user-readable list of their names
     *
     * @param  Player|Player[]|null $models
     * @param  \Closure             $getName A function that, given a model, returns its name
     * @return array
     */
    public function reverseTransform($models, $getName)
    {
        if (null === $models) {
            return $models;
        }

        if (!is_array($models)) {
            return $getName($models);
        }

        // Remove the player that we have to always include
        // Since he's always going to be there, don't show him to the user
        if ($this->include) {
            $include = $this->include->getId();
            $models  = array_filter($models, function ($player) use ($include) {
                return $player->getId() == $include;
            });
        }

        $models = array_map($getName, $models);

        foreach ($models as $model) {
            // If the array contains strings, sort it
            // Otherwise just return it
            if (!is_string($model)) {
                return $models;
            }
        }

        natcasesort($models);

        return $models;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('include'));
        $resolver->setDefaults(array(
            'compound' => true,
            'label'    => false,
            'multiple' => false,
        ));
    }

    public function getParent()
    {
        return 'form';
    }

    public function getName()
    {
        return 'player';
    }
}

class InvalidNameException extends \Exception
{
}
