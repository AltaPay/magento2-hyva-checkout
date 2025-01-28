<?php
/**
 * Altapay Module for Magento 2.x.
 *
 * Copyright © 2025 Altapay. All rights reserved.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Hyva_AltapayPayment', __DIR__);

