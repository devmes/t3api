<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Tests\Unit\Fixtures;

use SourceBroker\T3api\Annotation\Serializer\VirtualProperty;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

interface TaggableInterface
{
    public function getTags(): ObjectStorage;
}
