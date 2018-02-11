<?php
/**
 * This file contains a form creator for Servers
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\AdvancedModelType;
use BZIon\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for servers
 */
class ServerFormCreator extends ModelFormCreator
{
    const OFFICIAL_MATCH_SERVER = 'oms';
    const OFFICIAL_REPLAY_SERVER = 'ors';
    const PUBLIC_SERVER = 'ps';
    const PUBLIC_REPLAY_SERVER = 'prs';

    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        return $builder
            ->add('domain', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 50,
                    ]),
                ],
            ])
            ->add(
                $builder->create('port', IntegerType::class, array(
                    'constraints' => new NotBlank(),
                    'data'        => 5154
                ))->setDataLocked(false) // Don't lock the data so we can change the default value later if needed
            )
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'max' => 100,
                    ]),
                ],
            ])
            ->add('country', new ModelType('Country'), [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('owner', new AdvancedModelType('Player'), [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('server_type', ChoiceType::class, [
                'choices' => [
                    self::OFFICIAL_MATCH_SERVER  => 'Official Match Server',
                    self::OFFICIAL_REPLAY_SERVER => 'Official Replay Server',
                    self::PUBLIC_SERVER          => 'Public Server',
                    self::PUBLIC_REPLAY_SERVER   => 'Public Replay Server',
                ],
                'required' => true,
                'label' => 'Server Type',
            ])
            ->add('inactive', CheckboxType::class, [
                'label' => 'Server Inactive',
                'required' => false,
                'attr' => [
                    'data-help-message' => 'When checked, that means this server is no longer active in hosting',
                ],
            ])
            ->add('enter', SubmitType::class, [
                'attr' => [
                    'class' => 'c-button--blue pattern pattern--downward-stripes',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Server $server
     */
    public function fill($form, $server)
    {
        $form->get('name')->setData($server->getName());
        $form->get('domain')->setData($server->getDomain());
        $form->get('port')->setData($server->getPort());
        $form->get('country')->setData($server->getCountry());
        $form->get('owner')->setData($server->getOwner());
        $form->get('inactive')->setData($server->isInactive());

        $serverType = $form->get('server_type');

        if ($server->isOfficialServer()) {
            if ($server->isReplayServer()) {
                $serverType->setData(self::OFFICIAL_REPLAY_SERVER);
            }
            else {
                $serverType->setData(self::OFFICIAL_MATCH_SERVER);
            }
        }
        else {
            if ($server->isReplayServer()) {
                $serverType->setData(self::PUBLIC_REPLAY_SERVER);
            }
            else {
                $serverType->setData(self::PUBLIC_SERVER);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Server $server
     */
    public function update($form, $server)
    {
        $server
            ->setName($form->get('name')->getData())
            ->setDomain($form->get('domain')->getData())
            ->setPort($form->get('port')->getData())
            ->setCountry($form->get('country')->getData()->getId())
            ->setOwner($form->get('owner')->getData()->getId())
            ->setInactive($form->get('inactive')->getData())
        ;

        $this->updateServerType($server, $form->get('server_type')->getData());

        $server->forceUpdate();
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        $server = \Server::addServer(
            $form->get('name')->getData(),
            $form->get('domain')->getData(),
            $form->get('port')->getData(),
            $form->get('country')->getData()->getId(),
            $form->get('owner')->getData()->getId()
        );

        $server->setInactive($form->get('inactive')->getData());

        $this->updateServerType($server, $form->get('server_type')->getData());

        return $server;
    }

    private function updateServerType(\Server $server, $serverType)
    {
        switch ($serverType) {
            case self::OFFICIAL_MATCH_SERVER:
                $server->setOfficialServer(true);
                $server->setReplayServer(false);
                break;

            case self::OFFICIAL_REPLAY_SERVER:
                $server->setOfficialServer(true);
                $server->setReplayServer(true);
                break;

            case self::PUBLIC_SERVER:
                $server->setOfficialServer(false);
                $server->setReplayServer(false);
                break;

            case self::PUBLIC_REPLAY_SERVER:
                $server->setOfficialServer(false);
                $server->setReplayServer(true);
                break;
        }
    }
}
