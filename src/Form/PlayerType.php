<?php
namespace BZIon\Form;

use InvalidUsernameException;
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
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $data = $view->vars['value'];
        $view->children['players']->vars['value'] = $this->reverseTransform($data);
    }

    /**
     * Convert the vague array that the user gave us into meaningful models
     * @param FormEvent $event
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

        if ($listUsernames !== '0') {
            $players = $this->usernamesToModels($players, $form);
        } else {
            $players = $this->idsToModels($players, $form);
        }

        $event->setData($players);
    }

    /**
     * Convert a list of usernames to models
     *
     * Empty usernames are ignored
     *
     * @param string[] $usernames A list of usernames
     * @param Form $form A form to add errors to
     * @return Player[]
     */
    private function usernamesToModels($usernames, &$form)
    {
        $players = array();

        foreach ($usernames as $username) {
            if (empty($username)) continue;

            $player = Player::getFromUsername($username);

            if (!$player->isValid()) {
                // Symfony auto-escapes $username
                $message = "There is no player called $username";
                $form->addError(new FormError($message));
            } else {
                $players[] = $player;
            }
        }

        return $players;
    }

    /**
     * Convert a list of player IDs to models
     *
     * @param int[] $ids A list of player IDs
     * @param Form $form A form to add errors to
     * @return Player[]
     */
    private function idsToModels($ids, &$form)
    {
        $players = array();

        foreach ($ids as $id) {
            $id = (int) $id;
            $player = new Player($id);

            if (!$player->isValid()) {
                $message = "There is no player with ID $id";
                $form->addError(new FormError($message));
            } else {
                $players[] = $player;
            }
        }

        return $players;
    }

    /**
     * Converts an array of models into a user-readable list of their names
     *
     * @param  Model|Model[]|null $query
     * @return string|null
     */
    public function reverseTransform($models)
    {
        if (null === $models)
            return $models;

        $getName = function ($model) {
            if (!$model instanceof Model) return '';
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
