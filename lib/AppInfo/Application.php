<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\NewsLetterBuilder\AppInfo;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\Util;
use OCP\AppFramework\App;

class Application extends App implements IBootstrap {
	public const APP_ID = 'newsletterbuilder';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerNotifierService(Notifier::class);

	}

	public function boot(IBootContext $context): void {
		//Util::addStyle(self::APP_ID, 'grapes.min');
		//Util::addStyle(self::APP_ID, 'fontawesome.min');
		Util::addScript(self::APP_ID, 'newsletterbuilder-ckeditor');
		Util::addScript(self::APP_ID, 'newsletterbuilder-pluginCkeditor');
	}

}
