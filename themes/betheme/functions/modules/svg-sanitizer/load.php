<?php
/**
 * SVG sanitizer
 * https://github.com/darylldoyle/svg-sanitizer/tags
 * version 0.19.0
 */

require_once( __DIR__ . '/data/AttributeInterface.php' );
require_once( __DIR__ . '/data/TagInterface.php' );
require_once( __DIR__ . '/data/AllowedAttributes.php' );
require_once( __DIR__ . '/data/AllowedTags.php' );
require_once( __DIR__ . '/data/XPath.php' );
require_once( __DIR__ . '/ElementReference/Resolver.php' );
require_once( __DIR__ . '/ElementReference/Subject.php' );
require_once( __DIR__ . '/ElementReference/Usage.php' );
require_once( __DIR__ . '/Exceptions/NestingException.php' );
require_once( __DIR__ . '/Helper.php' );
require_once( __DIR__ . '/Sanitizer.php' );
