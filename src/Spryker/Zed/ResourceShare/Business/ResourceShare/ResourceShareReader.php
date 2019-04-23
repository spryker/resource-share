<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ResourceShare\Business\ResourceShare;

use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\ResourceShareRequestTransfer;
use Generated\Shared\Transfer\ResourceShareResponseTransfer;
use Spryker\Zed\ResourceShare\Persistence\ResourceShareRepositoryInterface;
use Spryker\Zed\ResourceShare\ResourceShareConfig;

class ResourceShareReader implements ResourceShareReaderInterface
{
    protected const GLOSSARY_KEY_RESOURCE_IS_NOT_FOUND_BY_PROVIDED_UUID = 'resource_share.reader.error.resource_is_not_found_by_provided_uuid';

    /**
     * @var \Spryker\Zed\ResourceShare\Persistence\ResourceShareRepositoryInterface
     */
    protected $resourceShareRepository;

    /**
     * @var \Spryker\Zed\ResourceShare\Business\ResourceShare\ResourceShareValidatorInterface
     */
    protected $resourceShareValidator;

    /**
     * @var \Spryker\Zed\ResourceShareExtension\Dependency\Plugin\ResourceShareResourceDataExpanderStrategyPluginInterface[]
     */
    protected $resourceShareResourceDataExpanderStrategyPlugins;

    /**
     * @param \Spryker\Zed\ResourceShare\Persistence\ResourceShareRepositoryInterface $resourceShareRepository
     * @param \Spryker\Zed\ResourceShare\Business\ResourceShare\ResourceShareValidatorInterface $resourceShareValidator
     * @param \Spryker\Zed\ResourceShareExtension\Dependency\Plugin\ResourceShareResourceDataExpanderStrategyPluginInterface[] $resourceShareResourceDataExpanderStrategyPlugins
     */
    public function __construct(
        ResourceShareRepositoryInterface $resourceShareRepository,
        ResourceShareValidatorInterface $resourceShareValidator,
        array $resourceShareResourceDataExpanderStrategyPlugins
    ) {
        $this->resourceShareRepository = $resourceShareRepository;
        $this->resourceShareValidator = $resourceShareValidator;
        $this->resourceShareResourceDataExpanderStrategyPlugins = $resourceShareResourceDataExpanderStrategyPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\ResourceShareRequestTransfer $resourceShareRequestTransfer
     *
     * @return \Generated\Shared\Transfer\ResourceShareResponseTransfer
     */
    public function getResourceShareByUuid(ResourceShareRequestTransfer $resourceShareRequestTransfer): ResourceShareResponseTransfer
    {
        $resourceShareRequestTransfer->requireResourceShare();
        $resourceShareTransfer = $resourceShareRequestTransfer->getResourceShare();
        $resourceShareTransfer->requireUuid();

        $resourceShareResponseTransfer = new ResourceShareResponseTransfer();
        $resourceShareTransfer = $this->resourceShareRepository->findResourceShareByUuid(
            $resourceShareTransfer->getUuid()
        );

        if (!$resourceShareTransfer) {
            return $resourceShareResponseTransfer->setIsSuccessful(false)
                ->addMessage(
                    (new MessageTransfer())
                        ->setType(ResourceShareConfig::ERROR_MESSAGE_TYPE)
                        ->setValue(static::GLOSSARY_KEY_RESOURCE_IS_NOT_FOUND_BY_PROVIDED_UUID)
                );
        }

        $resourceShareResponseTransfer = $this->resourceShareValidator->validateResourceShareTransfer($resourceShareTransfer);
        if (!$resourceShareResponseTransfer->getIsSuccessful()) {
            return $resourceShareResponseTransfer;
        }

        $resourceShareResponseTransfer->setIsSuccessful(true)
            ->setResourceShare($resourceShareTransfer);

        return $this->executeResourceDataExpanderStrategyPlugins($resourceShareResponseTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ResourceShareResponseTransfer $resourceShareResponseTransfer
     *
     * @return \Generated\Shared\Transfer\ResourceShareResponseTransfer
     */
    protected function executeResourceDataExpanderStrategyPlugins(ResourceShareResponseTransfer $resourceShareResponseTransfer): ResourceShareResponseTransfer
    {
        $resourceShareTransfer = $resourceShareResponseTransfer->getResourceShare();
        foreach ($this->resourceShareResourceDataExpanderStrategyPlugins as $resourceDataExpanderStrategyPlugin) {
            if (!$resourceDataExpanderStrategyPlugin->isApplicable($resourceShareTransfer)) {
                continue;
            }

            $strategyResourceShareResponseTransfer = $resourceDataExpanderStrategyPlugin->expand($resourceShareTransfer);
            if (!$strategyResourceShareResponseTransfer->getIsSuccessful()) {
                return $strategyResourceShareResponseTransfer;
            }

            return $resourceShareResponseTransfer->setResourceShare($strategyResourceShareResponseTransfer->getResourceShare());
        }
    }
}
