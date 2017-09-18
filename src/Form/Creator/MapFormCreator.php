<?php
/**
 * This file contains a form creator for Teams
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Constraint\UniqueAlias;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Form creator for teams
 *
 * @property \Map $editing
 */
class MapFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        $builder
            ->add('name', TextType::class, array(
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 40,
                    ]),
                ],
                'data'      => $this->editing->getName(),
                'required' => true,
            ))
            ->add('alias', TextType::class, array(
                'constraints' => [
                    new Length([
                        'max' => 40,
                    ]),
                    new UniqueAlias('Map', $this->editing),
                    new Type([
                        'type' => 'alpha'
                    ]),
                ],
                'data'     => $this->editing->getAlias(),
                'required' => true
            ))
            ->add('description', TextareaType::class, array(
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 10
                    ]),
                ],
                'data'     => $this->editing->getDescription(),
                'required' => true,
            ))
            ->add('avatar', FileType::class, array(
                'constraints' => new Image(array(
                    'minWidth'  => 60,
                    'minHeight' => 60,
                    'maxSize'   => '8M'
                )),
                'required' => ($this->editing === null || $this->editing->hasAvatar())
            ))
            ->add('shot_count', IntegerType::class, [
                'data'     => $this->editing->getShotCount(),
                'label'    => 'Max shot count',
                'required' => true,
            ])
            ->add('jumping', CheckboxType::class, [
                'data'     => $this->editing->isJumpingEnabled(),
                'label'    => 'Map allows jumping',
                'required' => false,
            ])
            ->add('ricochet', CheckboxType::class, [
                'data'     => $this->editing->isRicochetEnabled(),
                'label'    => 'Map allows ricochet',
                'required' => false,
            ])
        ;

        if ($this->editing) {
            // We are editing the map, not creating it
            $builder->add('delete_avatar', SubmitType::class);
        }

        return $builder->add('submit', SubmitType::class, [
            'label' => 'Save'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        return
            \Map::addMap(
                $form->get('name')->getData(),
                $form->get('alias')->getData(),
                $form->get('description')->getData()
            )
            ->setAvatarFile($form->get('avatar')->getData())
            ->setShotCount($form->get('shot_count')->getData())
            ->setJumpingEnabled($form->get('jumping')->getData())
            ->setRicochetEnabled($form->get('ricochet')->getData())
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Map $map
     */
    public function update($form, $map)
    {
        $map->setName($form->get('name')->getData());
        $map->setAlias($form->get('alias')->getData());
        $map->setDescription($form->get('description')->getData());
        $map->setShotCount($form->get('shot_count')->getData());
        $map->setJumpingEnabled($form->get('jumping')->getData());
        $map->setRicochetEnabled($form->get('ricochet')->getData());

        if ($form->has('delete_avatar') && $form->get('delete_avatar')->isClicked()) {
            $map->resetAvatar();
        } else {
            $map->setAvatarFile($form->get('avatar')->getData());
        }

        return $map;
    }
}
