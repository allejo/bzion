<?php
/**
 * This file contains a form creator for Bans
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Form\Creator;

use BZIon\Form\Type\AdvancedModelType;
use BZIon\Form\Type\IpType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form creator for bans
 *
 * @property \Ban $editing
 */
class BanFormCreator extends ModelFormCreator
{
    /**
     * {@inheritdoc}
     */
    protected function build($builder)
    {
        $builder
            ->add('player', new AdvancedModelType('player'), [
                'constraints' => [
                    new NotBlank()
                ],
                'disabled' => $this->isEdit(),
                'required' => true,
            ])
            ->add('expiration', DateType::class, [
                'data' => \TimeDate::now(),
                'label' => 'Expiration Date',
                'required' => false,
            ])
            ->add('is_permanent', CheckboxType::class, [
                'required' => false,
            ])
            ->add('reason', TextareaType::class, [
                'constraints' => [
                    new NotBlank()
                ],
                'required' => true,
            ])
            ->add('is_soft_ban', CheckboxType::class, [
                'label' => 'Soft Ban',
                'required' => false,
            ])
            ->add('server_message', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 150,
                    ]),
                ],
            ])
            ->add('ip_addresses', new IpType(), [
                'label' => 'IP Addresses',
                'required' => false,
            ])
        ;

        return $builder->add('submit', SubmitType::class, [
            'label' => 'Enter Ban',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ban $ban
     */
    public function fill($form, $ban)
    {
        $form->get('player')->setData($ban->getVictim());
        $form->get('is_permanent')->setData($ban->isPermanent());
        $form->get('reason')->setData($ban->getReason());
        $form->get('is_soft_ban')->setData($ban->isSoftBan());
        $form->get('server_message')->setData($ban->getServerMessage());
        $form->get('ip_addresses')->setData($ban->getIpAddresses());

        if (!$ban->isPermanent()) {
            $form->get('expiration')->setData($ban->getExpiration());
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Ban $ban
     */
    public function update($form, $ban)
    {
        $ban
            ->setIPs($form->get('ip_addresses')->getData())
            ->setExpiration($this->getExpiration($form))
            ->setReason($form->get('reason')->getData())
            ->setServerMessage($form->get('server_message')->getData())
            ->setSoftBan($form->get('is_soft_ban')->getData())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function enter($form)
    {
        return
            \Ban::addBan(
                $form->get('player')->getData()->getId(),
                $this->me->getId(),
                $this->getExpiration($form),
                $form->get('reason')->getData(),
                $form->get('server_message')->getData(),
                $form->get('ip_addresses')->getData(),
                $form->get('is_soft_ban')->getData()
            )
        ;
    }

    /**
     * Get the expiration time of the ban based on the fields of the form
     *
     * @param  Form          $form The form
     *
     * @return \TimeDate|null
     */
    private function getExpiration($form)
    {
        if ($form->get('is_permanent')->getData()) {
            return null;
        }

        return $form->get('expiration')->getData();
    }
}
