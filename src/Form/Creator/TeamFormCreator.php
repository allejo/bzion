<?php
/**
 * This file contains a form creator for Teams
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\ModelType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for teams
 */
class TeamFormCreator extends ModelFormCreator
{
    /**
     * {@inheritDoc}
     */
    protected function build($builder)
    {
        $builder->add('name', 'text', array(
            'constraints' => array(
                new NotBlank(), new Length(array(
                    'min' => 2,
                    'max' => 32, // default BZFlag motto length
                ))
            )
        ))->add('description', 'textarea', array(
            'required' => false
        ))->add('avatar', 'file', array(
            'constraints' => new Image(array(
                'minWidth'  => 60,
                'minHeight' => 60,
                'maxSize'   => '4M'
            )),
            'required' => false
        ));

        if ($this->editing) {
            // We are editing the team, not creating it
            $team = $this->editing;

            $builder->add('delete_avatar', 'submit');

            // Let the user appoint a different leader
            $builder->add('leader', new ModelType('Player', false, function ($query) use ($team) {
                // Only list players belonging in that team
                return $query->where('team')->is($team);
            }));
        }

        return $builder->add('status', 'choice', array(
                'choices' => array(
                    'open'   => 'Open',
                    'closed' => 'Closed',
                ),
            ))
            ->add('submit', 'submit');
    }

    /**
     * {@inheritDoc}
     */
    public function fill($form, $team)
    {
        $form->get('name')->setData($team->getName());
        $form->get('description')->setData($team->getDescription(true));
        $form->get('status')->setData($team->getStatus());
        $form->get('leader')->setData($team->getLeader());
    }

    /**
     * {@inheritDoc}
     */
    public function enter($form)
    {
        return \Team::createTeam(
            $form->get('name')->getData(),
            $this->me->getId(),
            '',
            $form->get('description')->getData(),
            $form->get('status')->getData()
        )->setAvatarFile($form->get('avatar')->getData());
    }

    /**
     * {@inheritDoc}
     */
    public function update($form, $team)
    {
        $team->setName($form->get('name')->getData());
        $team->setDescription($form->get('description')->getData());
        $team->setStatus($form->get('status')->getData());

        // Is the player updating the team's leader?
        // Don't let them do it right away - issue a confirmation notice first
        $leader = $form->get('leader')->getData();

        if ($leader->getId() != $team->getLeader()->getId()) {
            $this->controller->newLeader($leader);
        }

        if ($form->get('delete_avatar')->isClicked()) {
            $team->resetAvatar();
        } else {
            $team->setAvatarFile($form->get('avatar')->getData());
        }

        return $team;
    }
}
