<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\NewsLetterBuilder\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'resources' => [
		'newsletter' => ['url' => '/newsletters'],
		'newsletter_api' => ['url' => '/api/0.1/newsletters']
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		
		['name' => 'newsletter_api#preflighted_cors', 'url' => '/api/0.1/{path}',
			'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
		['name' => 'utils_api#sendEmail', 'url' => '/sendEmail', 'verb' => 'POST'],
		
	]
];
