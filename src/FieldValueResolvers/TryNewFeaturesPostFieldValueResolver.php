<?php
namespace PoP\BlockMetadataWP\FieldValueResolvers;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\Facades\Schema\FieldQueryInterpreterFacade;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\FieldValueResolvers\AbstractDBDataFieldValueResolver;
use PoP\ComponentModel\FieldResolvers\FieldResolverInterface;
use PoP\Posts\FieldResolvers\PostFieldResolver;

class TryNewFeaturesPostFieldValueResolver extends AbstractDBDataFieldValueResolver
{
    public static function getClassesToAttachTo(): array
    {
        return array(PostFieldResolver::class);
    }

    public function resolveCanProcess(FieldResolverInterface $fieldResolver, string $fieldName, array $fieldArgs = []): bool
    {
        return $fieldArgs['branch'] == 'try-new-features' && $fieldArgs['project'] == 'block-metadata';
    }

    public static function getFieldNamesToResolve(): array
    {
        return [
			'content',
        ];
    }

    public function getFieldDocumentationType(FieldResolverInterface $fieldResolver, string $fieldName): ?string
    {
        $types = [
			'content' => SchemaDefinition::TYPE_STRING,
        ];
        return $types[$fieldName] ?? parent::getFieldDocumentationType($fieldResolver, $fieldName);
    }

    public function getFieldDocumentationDescription(FieldResolverInterface $fieldResolver, string $fieldName): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        $descriptions = [
			'content' => $translationAPI->__('Post\'s content, formatted with its block metadata', 'pop-block-metadata'),
        ];
        return $descriptions[$fieldName] ?? parent::getFieldDocumentationDescription($fieldResolver, $fieldName);
    }

    public function resolveValue(FieldResolverInterface $fieldResolver, $resultItem, string $fieldName, array $fieldArgs = [])
    {
        switch ($fieldName) {
            case 'content':
                unset($fieldArgs['branch']);
                unset($fieldArgs['project']);
                return $fieldResolver->resolveValue($resultItem, FieldQueryInterpreterFacade::getInstance()->getField('block-metadata', $fieldArgs));
        }

        return parent::resolveValue($fieldResolver, $resultItem, $fieldName, $fieldArgs);
    }
}
