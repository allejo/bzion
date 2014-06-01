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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('players', 'text', array(
            'attr' => array(
                'class' => 'player-select',
                'placeholder' => 'brad, kierra, ...',
            ),
            'label' => false,
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
    }

    /**
     * Pass the image URL to the view
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $data = $view->vars['value'];
        $view->children['players']->vars['value'] = $this->reverseTransform($data);
    }

    /**
     * Convert the vague array that the user gave us into meaningful models
     * @param  FormEvent $event
     * @return void
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $players = $data['players'];
        $listUsernames = $data['ListUsernames'];
        $form = $event->getForm()->get('players');

        // Convert the comma-separated list of players the user gave us into an
        // array
        $players = explode(',', $players);

        // Remove all the whitespace and duplicate entries
        $players = array_map(function ($r) { return trim($r); }, $players);
        $players = array_unique($players);

        $models = array();

        foreach ($players as $player) {
            $model = ($listUsernames === '0')
                   ? $this->idToModel($player, $form)
                   : $this->usernameToModel($player, $form);

            if ($model)
                $models[] = $model;
        }

        $event->setData($models);
    }

    /**
     * Convert a username to a model
     *
     * Empty usernames are ignored
     *
     * @param  string        $usernames The username
     * @param  FormInterface $form      A form to add errors to
     * @return Player|null
     */
    private function usernameToModel($username, &$form)
    {
        if (empty($username)) return;

        $player = Player::getFromUsername($username);

        if (!$player->isValid()) {
            // Symfony auto-escapes $username
            $message = "There is no player called $username";
            $form->addError(new FormError($message));
        } else {
            return $player;
        }
    }

    /**
     * Convert a player ID to a model
     *
     * @param  int[]         $ids  A list of player IDs
     * @param  FormInterface $form A form to add errors to
     * @return Player|null
     */
    private function idToModel($id, &$form)
    {
        $id = (int) $id;
        $player = new Player($id);

        if (!$player->isValid()) {
            $message = "There is no player with ID $id";
            $form->addError(new FormError($message));
        } else {
            return $player;
        }
    }

    /**
     * Converts an array of models into a user-readable list of their names
     *
     * @param  Player|Player[]|null $query
     * @return string|null
     */
    public function reverseTransform($models)
    {
        if (null === $models)
            return $models;

        $getName = function ($model) {
            if (!$model instanceof Player) return '';
            return $model->getName();
        };

        if (!is_array($models))
            return $getName($models);

        $models = array_map($getName, $models);
        sort($models);

        return implode(', ', $models);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => true,
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
