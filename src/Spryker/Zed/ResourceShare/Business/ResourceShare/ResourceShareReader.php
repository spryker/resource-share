<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ResourceShare\Business\ResourceShare;

use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\ResourceShareResponseTransfer;
use Spryker\Zed\ResourceShare\Persistence\ResourceShareRepositoryInterface;

class ResourceShareReader implements ResourceShareReaderInterface
{
    protected const ERROR_MESSAGE_RESOURCE_IS_NOT_FOUND_BY_PROVIDED_UUID = 'Resource is not found by provided UUID.';

    /**
     * @var \Spryker\Zed\ResourceShare\Persistence\ResourceShareRepositoryInterface
     */
    protected $resourceShareRepository;

    /**
     * @param \Spryker\Zed\ResourceShare\Persistence\ResourceShareRepositoryInterface $resourceShareRepository
     */
    public function __construct(ResourceShareRepositoryInterface $resourceShareRepository)
    {
        $this->resourceShareRepository = $resourceShareRepository;
    }

    /**
     * @param string $uuid
     *
     * @return \Generated\Shared\Transfer\ResourceShareResponseTransfer
     */
    public function getResourceShareByUuid(string $uuid): ResourceShareResponseTransfer
    {
        $resourceShareResponseTransfer = new ResourceShareResponseTransfer();

        $resourceShareTransfer = $this->resourceShareRepository->findResourceShareByUuid($uuid);
        if ($resourceShareTransfer) {
            return $resourceShareResponseTransfer->setIsSuccessful(true)
                ->setResourceShare($resourceShareTransfer);
        }

        return $resourceShareResponseTransfer->setIsSuccessful(false)
            ->addErrorMessage(
                (new MessageTransfer())->setValue(static::ERROR_MESSAGE_RESOURCE_IS_NOT_FOUND_BY_PROVIDED_UUID)
            );
    }
}