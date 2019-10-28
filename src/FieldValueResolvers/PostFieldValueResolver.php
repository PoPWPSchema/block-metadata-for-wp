<?php
namespace PoP\BlockMetadataWP\FieldValueResolvers;
use Leoloso\BlockMetadata\Data;
use Leoloso\BlockMetadata\Metadata;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\FieldValueResolvers\AbstractDBDataFieldValueResolver;
use PoP\ComponentModel\FieldResolvers\FieldResolverInterface;

class PostFieldValueResolver extends AbstractDBDataFieldValueResolver
{
    public static function getClassesToAttachTo(): array
    {
        return array(\PoP\Posts\FieldResolver_Posts::class);
    }

    public static function getFieldNamesToResolve(): array
    {
        return [
			'block-metadata',
        ];
    }

    public function getFieldDocumentationType(FieldResolverInterface $fieldResolver, string $fieldName): ?string
    {
        $types = [
			'block-metadata' => SchemaDefinition::TYPE_OBJECT,
        ];
        return $types[$fieldName] ?? parent::getFieldDocumentationType($fieldResolver, $fieldName);
    }

    public function getFieldDocumentationDescription(FieldResolverInterface $fieldResolver, string $fieldName): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        $descriptions = [
			'block-metadata' => $translationAPI->__('Metadata for all blocks contained in the post, split on a block by block basis', 'pop-block-metadata'),
        ];
        return $descriptions[$fieldName] ?? parent::getFieldDocumentationDescription($fieldResolver, $fieldName);
    }

    public function getFieldDocumentationArgs(FieldResolverInterface $fieldResolver, string $fieldName): array
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        switch ($fieldName) {
            case 'block-metadata':
                    return [
                        [
                            SchemaDefinition::ARGNAME_NAME => 'blockname',
                            SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_STRING,
                            SchemaDefinition::ARGNAME_DESCRIPTION => $translationAPI->__('Fetch only the block with this name in the post, filtering out all other blocks', 'block-metadata'),
                        ],
                    ];
        }

        return parent::getFieldDocumentationArgs($fieldResolver, $fieldName);
    }

    public function resolveValue(FieldResolverInterface $fieldResolver, $resultItem, string $fieldName, array $fieldArgs = [])
    {
        $post = $resultItem;
        switch ($fieldName) {
            case 'block-metadata':
                $block_data = Data::get_block_data($post->post_content);
                $block_metadata = Metadata::get_block_metadata($block_data);

                // Filter by blockName
                if ($blockName = $fieldArgs['blockname']) {
                    $block_metadata = array_filter(
                        $block_metadata,
                        function($block) use($blockName) {
                            return $block['blockName'] == $blockName;
                        }
                    );
                }
                return $block_metadata;
        }

        return parent::resolveValue($fieldResolver, $resultItem, $fieldName, $fieldArgs);
    }
}
