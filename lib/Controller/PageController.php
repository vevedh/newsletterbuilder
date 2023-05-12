<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\NewsLetterBuilder\Controller;

use OCA\NewsLetterBuilder\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {
	public function __construct(IRequest $request) {
		parent::__construct(Application::APP_ID, $request,'PUT, POST, GET, DELETE, PATCH','Content-Security-Policy,img-src,none');
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): TemplateResponse {
		Util::addScript(Application::APP_ID, 'newsletterbuilder-main');

		$response = new TemplateResponse(Application::APP_ID, 'main');
		
		$response->addHeader('X-Frame-Options', 'ALLOW');
		$policy = new ContentSecurityPolicy();
		$policy->addAllowedImageDomain('http:');
		$policy->addAllowedFrameAncestorDomain('*');
		$policy->addAllowedWorkerSrcDomain('*');
		$policy->addAllowedFrameDomain('*');
		$response->setContentSecurityPolicy($policy);

		//return new TemplateResponse(Application::APP_ID, 'main');
		return $response;
	}
}
