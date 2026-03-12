<?php

declare(strict_types=1);

namespace Tempest\Support\Math;

use const INF;
use const NAN as PHP_NAN;

/**
 * The value of `INFINITY` is `1 / 0` (positive infinity).
 *
 * @var int
 */
const INFINITY = INF;

/**
 * The value of `NAN` is `0 / 0` (not a number).
 *
 * @var float
 */
const NAN = PHP_NAN;

/**
 * The base of the natural system of logarithms, or approximately 2.7182818284590452353602875.
 *
 * @var float
 */
const E = 2.718_281_828_459_045_235_360_287_5;

/**
 * The ratio of the circumference of a circle to its diameter, or approximately 3.141592653589793238462643.
 *
 * @var float
 */
const PI = 3.141_592_653_589_793_238_462_643;

/**
 * The maximum integer value representable in a 64-bit binary-coded decimal.
 *
 * @var int
 */
const INT64_MAX = 9_223_372_036_854_775_807;

/**
 * The minimum integer value representable in a 64-bit binary-coded decimal.
 *
 * @var int
 */
const INT64_MIN = -1 << 63;

/**
 * The maximum integer value representable in a 53-bit binary-coded decimal.
 *
 * @var int
 */
const INT53_MAX = 9_007_199_254_740_992;

/**
 * The minimum integer value representable in a 53-bit binary-coded decimal.
 *
 * @var int
 */
const INT53_MIN = -9_007_199_254_740_993;

/**
 * The maximum integer value representable in a 32-bit binary-coded decimal.
 *
 * @var int
 */
const INT32_MAX = 2_147_483_647;

/**
 * The minimum integer value representable in a 32-bit binary-coded decimal.
 *
 * @var int
 */
const INT32_MIN = -2_147_483_648;

/**
 * The maximum integer value representable in a 16-bit binary-coded decimal.
 *
 * @var int
 */
const INT16_MAX = 32_767;

/**
 * The minimum integer value representable in a 16-bit binary-coded decimal.
 *
 * @var int
 */
const INT16_MIN = -32_768;

/**
 * The maximum integer value representable in a 8-bit binary-coded decimal.
 *
 * @var int
 */
const INT8_MAX = 127;

/**
 * The minimum integer value representable in a 8-bit binary-coded decimal.
 *
 * @var int
 */
const INT8_MIN = -128;

/**
 * The maximum unsigned integer value representable in a 32-bit binary-coded decimal.
 *
 * @var int
 */
const UINT32_MAX = 4_294_967_295;

/**
 * The maximum unsigned integer value representable in a 16-bit binary-coded decimal.
 *
 * @var int
 */
const UINT16_MAX = 65_535;

/**
 * The maximum unsigned integer value representable in a 8-bit binary-coded decimal.
 *
 * @var int
 */
const UINT8_MAX = 255;

/**
 * The maximum floating point value representable in a 32-bit binary-coded decimal.
 *
 * @var float
 */
const FLOAT32_MAX = 3.402_823_47E+38;

/**
 * The minimum floating point value representable in a 32-bit binary-coded decimal.
 *
 * @var float
 */
const FLOAT32_MIN = -3.402_823_47E+38;

/**
 * The maximum floating point value representable in a 64-bit binary-coded decimal.
 *
 * @var float
 */
const FLOAT64_MAX = 1.797_693_134_862_315_7E+308;

/**
 * The minimum floating point value representable in a 64-bit binary-coded decimal.
 *
 * @var float
 */
const FLOAT64_MIN = -1.797_693_134_862_315_7E+308;
