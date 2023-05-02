<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\NewsLetterBuilder\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'newsletterbuilder';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
