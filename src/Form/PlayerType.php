<?php
namespace BZIon\Form;

use Model;
use Player;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('players', 'text', array(
            'attr' => array(
                'class' => 'player-select',
                'placeholder' => 'brad, kierra, ...',
            ),
            'label' => ucfirst($builder->getName()) . ': ',
            'required' => false
        ));

        // True if the client provided the recipient usernames instead of IDs
        // (to support non-JS browsers)
        $builder->add('ListUsernames', 'hidden', array(
            'attr' => array(
                'class' => 'player-select-type',
            ),
            'data' => true,
        ));

        $builder->addEventListener(FormEvents::SUBMIT, array($this, 'onSubmit'));

        if (isset($options['include']))
            $this->include = $options['include'];
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
        $newValue = null;

        if ($this->listUsernames) {
            // The user doesn't have javascript enabled - show a text field with
            // a comma-separated list of usernames
            $newValue = implode(', ', $this->reverseTransform($data,
                function ($player) { return $player->getUsername(); }
            ));
        } elseif ($form->isSubmitted()) {
            // The user has javascript enabled - set the value to a JSON array
            // that the client can read to fill the field with the values
            // selected by the user when the form was first submitted
            $newValue = json_encode($this->reverseTransform($data,
                function ($player) { return array(
                    'id' => $player->getId(),
                    'username' => $player->getUsername()
                );
            }));
        }

        $view->children['players']->vars['value'] = $newValue;
    }

    /**
     * Convert the vague array that the user gave us into meaningful models
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
            // The user didn't include any players, just set data to be an empty
            // array so that we do foreach() without any tricks
            $data = array();
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
        $players = array_map(function ($r) { return trim($r); }, $players);
        $players = array_unique($players);

        $models = array();

        foreach ($players as $player) {
            try {
                $model = ($this->listUsernames)
                       ? $this->usernameToModel($player)
                       : $this->idToModel($player);

                if ($model) {
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

        if ($this->include)
            $models[] = $this->include;

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
        if (empty($username)) return;

        $player = Player::getFromUsername($username);

        if (!$player->isValid())
            // Symfony auto-escapes $username
            throw new InvalidNameException("There is no player called $username");

        return $player;
    }

    /**
     * Convert a player ID to a model
     *
     * @throws InvalidNameException
     * @param  int                  $id The player ID
     * @return Player|null
     */
    private function idToModel($id)
    {
        $id = (int) $id;
        $player = new Player($id);

        if (!$player->isValid())
            throw new InvalidNameException("There is no player with ID $id");

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
        if (null === $models)
            return $models;

        if (!is_array($models))
            return $getName($models);

        $models = array_map($getName, $models);
        sort($models);

        return $models;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('include'));
        $resolver->setDefaults(array(
            'compound' => true,
            'label' => false
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

class InvalidNameException extends \Exception {}
