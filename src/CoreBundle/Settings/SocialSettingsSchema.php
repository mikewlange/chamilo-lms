<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Settings;

use Chamilo\CoreBundle\Form\Type\YesNoType;
use Sylius\Bundle\SettingsBundle\Schema\SchemaInterface;
use Sylius\Bundle\SettingsBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SocialSettingsSchema
 * @package Chamilo\CoreBundle\Settings
 */
class SocialSettingsSchema extends AbstractSettingsSchema
{
    /**
     * {@inheritdoc}
     */
    public function buildSettings(SettingsBuilderInterface $builder)
    {
        $builder
            ->setDefaults(
                [
                    'allow_social_tool' => 'true',
                    'allow_students_to_create_groups_in_social' => 'false',

                ]
            );
        $allowedTypes = [
            'allow_social_tool' => ['string'],
            'allow_students_to_create_groups_in_social' => ['string'],
        ];
        $this->setMultipleAllowedTypes($allowedTypes, $builder);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('allow_social_tool', YesNoType::class)
            ->add('allow_students_to_create_groups_in_social', YesNoType::class);
    }
}
