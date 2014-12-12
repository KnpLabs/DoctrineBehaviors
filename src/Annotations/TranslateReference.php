<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Annotations;
/**
 * TranslateReference annotation
 * @Annotation
 * @Target("CLASS")
 */
class TranslateReference {
    public $translatableClass;
    public $translationClass;
}