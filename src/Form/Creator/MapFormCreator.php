<?php
/**
 * This file contains a form creator for Teams
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Constraint\UniqueAlias;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
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
                'label'    => 'Mapchange Configuration Name',
                'required' => true
            ))
            ->add('description', TextareaType::class, array(
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 10
                    ]),
                ],
                'required' => true,
            ))
            ->add('world_size', IntegerType::class, [
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 200
                    ]),
                ],
                'label'    => 'BZDB _worldSize',
                'required' => true,
            ])
            ->add('randomly_generated', CheckboxType::class, [
                'label'    => 'Map is randomly generated',
                'required' => false,
            ])
            ->add('avatar', FileType::class, array(
                'constraints' => new Image(array(
                    'minWidth'  => 60,
                    'minHeight' => 60,
                    'maxSize'   => '8M'
                )),
                'required' => ($this->editing === null || $this->editing->hasAvatar())
            ))
            ->add('shot_count', IntegerType::class, [
                'constraints' => [
                    new GreaterThan([
                        'value' => 0
                    ]),
                ],
                'label'    => 'Max shot count',
                'required' => true,
            ])
            ->add('jumping', CheckboxType::class, [
                'label'    => 'Map allows jumping',
                'required' => false,
            ])
            ->add('ricochet', CheckboxType::class, [
                'label'    => 'Map allows ricochet',
                'required' => false,
            ])
            ->add('game_mode', ChoiceType::class, [
                'choices' => [
                    \Map::GAME_MODE_CTF  => 'CTF',
                    \Map::GAME_MODE_AHOD => 'AHOD',
                ],
                'multiple' => false,
                'label'    => 'Game Mode',
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
     *
     * @param \Map $map
     */
    public function fill($form, $map)
    {
        $form->get('name')->setData($map->getName());
        $form->get('alias')->setData($map->getAlias());
        $form->get('description')->setData($map->getDescription());
        $form->get('world_size')->setData($map->getWorldSize());
        $form->get('randomly_generated')->setData($map->isRandomlyGenerated());
        $form->get('shot_count')->setData($map->getShotCount());
        $form->get('jumping')->setData($map->isJumpingEnabled());
        $form->get('ricochet')->setData($map->isRicochetEnabled());
        $form->get('game_mode')->setData($map->getGameMode());
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
            ->setWorldSize($form->get('world_size')->getData())
            ->setRandomlyGenerated($form->get('randomly_generated')->getData())
            ->setGameMode($form->get('game_mode')->getData())
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
        $map->setWorldSize($form->get('world_size')->getData());
        $map->setRandomlyGenerated($form->get('randomly_generated')->getData());
        $map->setShotCount($form->get('shot_count')->getData());
        $map->setJumpingEnabled($form->get('jumping')->getData());
        $map->setRicochetEnabled($form->get('ricochet')->getData());
        $map->setGameMode($form->get('game_mode')->getData());

        if ($form->has('delete_avatar') && $form->get('delete_avatar')->isClicked()) {
            $map->resetAvatar();
        } else {
            $map->setAvatarFile($form->get('avatar')->getData());
        }

        return $map;
    }
}
