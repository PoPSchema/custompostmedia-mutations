<?php

declare(strict_types=1);

namespace PoPSchema\CustomPostMediaMutations\Hooks;

use PoP\Hooks\AbstractHookSet;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoPSchema\Media\TypeResolvers\MediaTypeResolver;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoPSchema\CustomPostMutations\Schema\SchemaDefinitionHelpers;
use PoPSchema\CustomPostMediaMutations\Facades\CustomPostMediaTypeAPIFacade;
use PoPSchema\CustomPostMediaMutations\MutationResolvers\MutationInputProperties;

class CustomPostMutationResolverHooks extends AbstractHookSet
{
    protected function init()
    {
        $this->hooksAPI->addFilter(
            SchemaDefinitionHelpers::HOOK_UPDATE_SCHEMA_FIELD_ARGS,
            array($this, 'getSchemaFieldArgs'),
            10,
            3
        );
        $this->hooksAPI->addAction(
            'gd_createupdate_post',
            array($this, 'setOrRemoveFeaturedImage'),
            10,
            2
        );
    }

    public function getSchemaFieldArgs(
        array $fieldArgs,
        TypeResolverInterface $typeResolver,
        string $fieldName
    ): array {
        $fieldArgs[] = [
            SchemaDefinition::ARGNAME_NAME => MutationInputProperties::FEATUREDIMAGE_ID,
            SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_ID,
            SchemaDefinition::ARGNAME_DESCRIPTION => sprintf(
                $this->translationAPI->__('The ID of the featured image (of type %s)', 'custompost-mutations'),
                MediaTypeResolver::NAME
            ),
        ];
        return $fieldArgs;
    }

    /**
     * If entry "featuredImageID" has an ID, set it. If it is null, remove it
     *
     * @param mixed $customPostID
     * @param mixed $form_data
     */
    public function setOrRemoveFeaturedImage($customPostID, $form_data): void
    {
        $customPostMediaTypeAPI = CustomPostMediaTypeAPIFacade::getInstance();
        if (isset($form_data[MutationInputProperties::FEATUREDIMAGE_ID])) {
            if ($featuredImageID = $form_data[MutationInputProperties::FEATUREDIMAGE_ID]) {
                $customPostMediaTypeAPI->setFeaturedImage($customPostID, $featuredImageID);
            } else {
                $customPostMediaTypeAPI->removeFeaturedImage($customPostID);
            }
        }
    }
}
