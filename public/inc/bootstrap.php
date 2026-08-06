<?php
/**
 * Single entry bootstrap for ERPH pages.
 */
require_once __DIR__ . '/kernel.php';
require_once __DIR__ . '/gateways.php';
require_once __DIR__ . '/payload.php';
require_once __DIR__ . '/glyphs.php';

bootstrap_session();
bootstrap_lexicon();
