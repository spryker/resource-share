<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ResourceShare\Business;

use Codeception\TestCase\Test;
use Generated\Shared\DataBuilder\ResourceShareBuilder;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ResourceShareRequestTransfer;
use Generated\Shared\Transfer\ResourceShareResponseTransfer;
use Generated\Shared\Transfer\ResourceShareTransfer;
use Spryker\Zed\ResourceShare\ResourceShareDependencyProvider;
use Spryker\Zed\ResourceShareExtension\Dependency\Plugin\ResourceShareActivatorStrategyPluginInterface;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Zed
 * @group ResourceShare
 * @group Business
 * @group Facade
 * @group ResourceShareFacadeTest
 * Add your own group annotations below this line
 */
class ResourceShareFacadeTest extends Test
{
    protected const GLOSSARY_KEY_RESOURCE_TYPE_IS_NOT_DEFINED = 'resource_share.generation.error.resource_type_is_not_defined';
    protected const GLOSSARY_KEY_RESOURCE_DATA_IS_NOT_DEFINED = 'resource_share.generation.error.resource_data_is_not_defined';
    protected const GLOSSARY_KEY_CUSTOMER_REFERENCE_IS_NOT_DEFINED = 'resource_share.generation.error.customer_reference_is_not_defined';
    protected const GLOSSARY_KEY_RESOURCE_IS_ALREADY_SHARED = 'resource_share.generation.error.resource_is_already_shared';
    protected const GLOSSARY_KEY_RESOURCE_IS_NOT_FOUND_BY_PROVIDED_UUID = 'resource_share.reader.error.resource_is_not_found';
    protected const GLOSSARY_KEY_STRATEGY_EXPECTS_LOGGED_IN_CUSTOMER = 'resource_share.activator.error.strategy_expects_logged_in_customer';

    protected const VALUE_RESOURCE_SHARE_UUID = 'VALUE_RESOURCE_SHARE_UUID';
    protected const VALUE_CUSTOMER_REFERENCE = 'VALUE_CUSTOMER_REFERENCE';

    /**
     * @var \SprykerTest\Zed\ResourceShare\ResourceShareBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testGenerateResourceShareShouldGenerateResourceWhenResourceTypeAndResourceDataAreUnique(): void
    {
        // Arrange
        $resourceShareTransfer = (new ResourceShareBuilder())->build();

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->generateResourceShare($resourceShareTransfer);

        // Assert
        $this->assertTrue($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertNotNull($resourceShareResponseTransfer->getResourceShare());
    }

    /**
     * @return void
     */
    public function testGenerateResourceShareShouldAddErrorMessageWhenResourceTypeIsNotDefined(): void
    {
        // Arrange
        $resourceShareTransfer = $this->tester->haveResourceShare();
        $resourceShareTransfer->setResourceType(null);

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->generateResourceShare($resourceShareTransfer);

        // Assert
        $this->assertFalse($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertTrue($this->hasResourceShareResponseTransferErrorMessage(
            $resourceShareResponseTransfer,
            static::GLOSSARY_KEY_RESOURCE_TYPE_IS_NOT_DEFINED
        ));
    }

    /**
     * @return void
     */
    public function testGenerateResourceShareShouldAllowResourceGenerationWithEmptyResourceData(): void
    {
        // Arrange
        $resourceShareTransfer = (new ResourceShareBuilder())->build();
        $resourceShareTransfer->setResourceData(null);

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->generateResourceShare($resourceShareTransfer);

        // Assert
        $this->assertTrue($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertEmpty($resourceShareResponseTransfer->getErrorMessages());
    }

    /**
     * @return void
     */
    public function testGenerateResourceShareShouldAddErrorMessageWhenCustomerReferenceIsNotDefined(): void
    {
        // Arrange
        $resourceShareTransfer = $this->tester->haveResourceShare();
        $resourceShareTransfer->setCustomerReference(null);

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->generateResourceShare($resourceShareTransfer);

        // Assert
        $this->assertFalse($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertTrue($this->hasResourceShareResponseTransferErrorMessage(
            $resourceShareResponseTransfer,
            static::GLOSSARY_KEY_CUSTOMER_REFERENCE_IS_NOT_DEFINED
        ));
    }

    /**
     * @return void
     */
    public function testGenerateResourceShareShouldAddErrorMessageWhenResourceIsAlreadyShared(): void
    {
        // Arrange
        $resourceShareTransfer = $this->tester->haveResourceShare();

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->generateResourceShare($resourceShareTransfer);

        // Assert
        $this->assertFalse($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertTrue($this->hasResourceShareResponseTransferErrorMessage(
            $resourceShareResponseTransfer,
            static::GLOSSARY_KEY_RESOURCE_IS_ALREADY_SHARED
        ));
    }

    /**
     * @return void
     */
    public function testActivateResourceShareShouldAddErrorMessageWhenResourceIsNotFoundByProvidedUuid(): void
    {
        // Arrange
        $customerTransfer = (new CustomerTransfer())
            ->setCustomerReference(static::VALUE_CUSTOMER_REFERENCE);

        $resourceShareRequestTransfer = (new ResourceShareRequestTransfer())
            ->setUuid(static::VALUE_RESOURCE_SHARE_UUID)
            ->setCustomer($customerTransfer);

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->activateResourceShare($resourceShareRequestTransfer);

        // Assert
        $this->assertFalse($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertTrue($this->hasResourceShareResponseTransferErrorMessage(
            $resourceShareResponseTransfer,
            static::GLOSSARY_KEY_RESOURCE_IS_NOT_FOUND_BY_PROVIDED_UUID
        ));
    }

    /**
     * @return void
     */
    public function testActivateResourceShareShouldNotSetIsLoginRequiredWhenCustomerIsLoggedInAndStrategyRequiresSo(): void
    {
        // Arrange
        $resourceShareTransfer = $this->tester->haveResourceShare();

        $resourceShareActivatorStrategyPluginMock = $this->createResourceShareActivatorStrategyPluginMock();
        $resourceShareActivatorStrategyPluginMock->method('isLoginRequired')->willReturn(true);
        $resourceShareActivatorStrategyPluginMock->expects($this->once())->method('execute');

        $this->registerResourceShareActivatorStrategyPlugin($resourceShareActivatorStrategyPluginMock);

        $customerTransfer = (new CustomerTransfer())
            ->setCustomerReference($resourceShareTransfer->getCustomerReference())
            ->setIsGuest(false);

        $resourceShareRequestTransfer = (new ResourceShareRequestTransfer())
            ->setUuid($resourceShareTransfer->getUuid())
            ->setCustomer($customerTransfer);

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->activateResourceShare($resourceShareRequestTransfer);

        // Assert
        $this->assertTrue($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertFalse($resourceShareResponseTransfer->getIsLoginRequired());
    }

    /**
     * @return void
     */
    public function testActivateResourceShareShouldSetIsLoginRequiredWhenCustomerIsNotLoggedInButStrategyRequiresSo(): void
    {
        // Arrange
        $resourceShareTransfer = $this->tester->haveResourceShare();

        $resourceShareActivatorStrategyPluginMock = $this->createResourceShareActivatorStrategyPluginMock();
        $resourceShareActivatorStrategyPluginMock->method('isLoginRequired')->willReturn(true);
        $resourceShareActivatorStrategyPluginMock->expects($this->never())->method('execute');

        $this->registerResourceShareActivatorStrategyPlugin($resourceShareActivatorStrategyPluginMock);

        $customerTransfer = (new CustomerTransfer())
            ->setCustomerReference($resourceShareTransfer->getCustomerReference())
            ->setIsGuest(true);

        $resourceShareRequestTransfer = (new ResourceShareRequestTransfer())
            ->setUuid($resourceShareTransfer->getUuid())
            ->setCustomer($customerTransfer);

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->activateResourceShare($resourceShareRequestTransfer);

        // Assert
        $this->assertFalse($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertTrue($resourceShareResponseTransfer->getIsLoginRequired());
        $this->assertTrue($this->hasResourceShareResponseTransferErrorMessage(
            $resourceShareResponseTransfer,
            static::GLOSSARY_KEY_STRATEGY_EXPECTS_LOGGED_IN_CUSTOMER
        ));
    }

    /**
     * @return void
     */
    public function testActivateResourceShareShouldExecuteStrategyPluginsWhenTheyDoNotRequireCustomerToBeLoggedIn(): void
    {
        // Arrange
        $resourceShareTransfer = $this->tester->haveResourceShare();

        $resourceShareActivatorStrategyPluginMock = $this->createResourceShareActivatorStrategyPluginMock();
        $resourceShareActivatorStrategyPluginMock->method('isLoginRequired')->willReturn(false);
        $resourceShareActivatorStrategyPluginMock->expects($this->once())->method('execute');

        $this->registerResourceShareActivatorStrategyPlugin($resourceShareActivatorStrategyPluginMock);

        $customerTransfer = (new CustomerTransfer())
            ->setCustomerReference($resourceShareTransfer->getCustomerReference())
            ->setIsGuest(true);

        $resourceShareRequestTransfer = (new ResourceShareRequestTransfer())
            ->setUuid($resourceShareTransfer->getUuid())
            ->setCustomer($customerTransfer);

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->activateResourceShare($resourceShareRequestTransfer);

        // Assert
        $this->assertTrue($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertFalse($resourceShareResponseTransfer->getIsLoginRequired());
    }

    /**
     * @return void
     */
    public function testGetResourceShareByUuidShouldAddErrorMessageWhenResourceIsNotFoundByProvidedUuid(): void
    {
        // Arrange
        $resourceShareTransfer = (new ResourceShareTransfer())
            ->setUuid(static::VALUE_RESOURCE_SHARE_UUID);

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->getResourceShareByUuid($resourceShareTransfer);

        // Assert
        $this->assertFalse($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertTrue($this->hasResourceShareResponseTransferErrorMessage(
            $resourceShareResponseTransfer,
            static::GLOSSARY_KEY_RESOURCE_IS_NOT_FOUND_BY_PROVIDED_UUID
        ));
    }

    /**
     * @return void
     */
    public function testGetResourceShareByUuidShouldReturnCorrectResourceShareFromDatabase(): void
    {
        // Arrange
        $originalResourceShareTransfer = $this->tester->haveResourceShare();

        $requestResourceShareTransfer = (new ResourceShareTransfer())
            ->setUuid($originalResourceShareTransfer->getUuid());

        // Act
        $resourceShareResponseTransfer = $this->getFacade()->getResourceShareByUuid($requestResourceShareTransfer);

        // Assert
        $this->assertTrue($resourceShareResponseTransfer->getIsSuccessful());
        $this->assertEquals(
            $originalResourceShareTransfer->getIdResourceShare(),
            $resourceShareResponseTransfer->getResourceShare()->getIdResourceShare()
        );
    }

    /**
     * @param \Spryker\Zed\ResourceShareExtension\Dependency\Plugin\ResourceShareActivatorStrategyPluginInterface $resourceShareActivatorStrategyPlugin
     *
     * @return void
     */
    protected function registerResourceShareActivatorStrategyPlugin(
        ResourceShareActivatorStrategyPluginInterface $resourceShareActivatorStrategyPlugin
    ): void {
        $this->tester->setDependency(ResourceShareDependencyProvider::PLUGINS_RESOURCE_SHARE_ACTIVATOR_STRATEGY, [
            $resourceShareActivatorStrategyPlugin,
        ]);
    }

    /**
     * @param \Generated\Shared\Transfer\ResourceShareResponseTransfer $resourceShareResponseTransfer
     * @param string $errorMessage
     *
     * @return bool
     */
    protected function hasResourceShareResponseTransferErrorMessage(
        ResourceShareResponseTransfer $resourceShareResponseTransfer,
        string $errorMessage
    ): bool {
        $resourceShareResponseTransfer->requireErrorMessages();
        foreach ($resourceShareResponseTransfer->getErrorMessages() as $messageTransfer) {
            $messageTransfer->requireValue();
            if ($messageTransfer->getValue() === $errorMessage) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Spryker\Zed\ResourceShareExtension\Dependency\Plugin\ResourceShareActivatorStrategyPluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createResourceShareActivatorStrategyPluginMock()
    {
        return $this->createMock(ResourceShareActivatorStrategyPluginInterface::class);
    }

    /**
     * @return \Spryker\Zed\ResourceShare\Business\ResourceShareFacadeInterface|\Spryker\Zed\Kernel\Business\AbstractFacade
     */
    protected function getFacade()
    {
        return $this->tester->getFacade();
    }
}
