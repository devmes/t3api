<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Handler;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Traversable;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ImageHandler
 */
class ImageHandler extends AbstractHandler implements SerializeHandlerInterface
{
    public const TYPE = 'Image';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [self::TYPE];

    /**
     * @param SerializationVisitorInterface $visitor
     * @param FileReference|FileReference[]|int|int[] $fileReference
     * @param array $type
     * @param SerializationContext $context
     *
     * @return string|string[]
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        $fileReference,
        array $type,
        SerializationContext $context
    ) {
        if (is_iterable($fileReference)) {
            return array_values(
                array_map(
                    function ($fileReference) use ($type) {
                        return $this->processSingleImage($fileReference, $type);
                    },
                    $fileReference instanceof Traversable ? iterator_to_array($fileReference) : $fileReference
                )
            );
        }

        return $this->processSingleImage($fileReference, $type);
    }

    /**
     * @param FileReference|int $fileReference
     * @param array $type
     * @return string
     */
    protected function processSingleImage($fileReference, array $type): string
    {
        if (is_int($fileReference)) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $fileRepository = $objectManager->get(FileRepository::class);
            $fileResource = $fileRepository->findFileReferenceByUid($fileReference);
        } else {
            $fileResource = $fileReference->getOriginalResource();
        }

        $file = $fileResource->getOriginalFile();
        $file = $file->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, [
            'width' => $type['params'][0] ?? '',
            'height' => $type['params'][1] ?? '',
        ]);

        return GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $file->getPublicUrl();
    }
}
