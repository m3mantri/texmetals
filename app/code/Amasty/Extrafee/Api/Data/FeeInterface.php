<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */

namespace Amasty\Extrafee\Api\Data;

/**
 * Class FeeInterface
 *
 * @author Artem Brunevski
 */

interface FeeInterface
{
    const ENTITY_ID = 'entity_id';
    const ENABLED = 'enabled';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const OPTIONS = 'options';
    const BASE_OPTIONS = 'base_options';
    const CURRENT_VALUE = 'current_value';
    const FRONTEND_TYPE = 'frontend_type';
    const DISCOUNT_IN_SUBTOTAL = 'discount_in_subtotal';
    const TAX_IN_SUBTOTAL = 'tax_in_subtotal';
    const SHIPPING_IN_SUBTOTAL = 'shipping_in_subtotal';


    /**
     * Get ID
     * @return int|null
     */
    public function getId();

    /**
     * Get enabled
     * @return bool
     */
    public function getEnabled();

    /**
     * Get name
     * @return string
     */
    public function getName();

    /**
     * Get description
     * @return string
     */
    public function getDescription();

    /**
     * Get fees optinos
     * @return string
     */
    public function getOptions();

    /**
     * Get current value
     * @return string
     */
    public function getCurrentValue();

    /**
     * Get fees base options
     * @return string
     */
    public function getBaseOptions();

    /**
     * Get $frontendType
     * @return string
     */
    public function getFrontendType();

    /**
     * @return mixed
     */
    public function getDiscountInSubtotal();

    /**
     * @return mixed
     */
    public function getTaxInSubtotal();

    /**
     * @return mixed
     */
    public function getShippingInSubtotal();

    /**
     * @param bool $enabled
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setEnabled($enabled);

    /**
     * @param string $name
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setName($name);

    /**
     * @param string $description
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setDescription($description);

    /**
     * @param mixed $options
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setOptions($options);

    /**
     * @param mixed $baseOptions
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setBaseOptions($baseOptions);

    /**
     * @param mixed $currentValue
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setCurrentValue($currentValue);

    /**
     * @param string $frontendType
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setFrontendType($frontendType);

    /**
     * @param mixed $discountInSubtotal
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setDiscountInSubtotal($discountInSubtotal);

    /**
     * @param mixed $taxInSubtotal
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setTaxInSubtotal($taxInSubtotal);

    /**
     * @param mixed $shippingInSubtotal
     * @return \Amasty\Extrafee\Api\Data\FeeInterface
     */
    public function setShippingInSubtotal($shippingInSubtotal);
}