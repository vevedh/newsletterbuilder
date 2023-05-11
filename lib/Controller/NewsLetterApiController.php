<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\NewsLetterBuilder\Controller;

use OCA\NewsLetterBuilder\AppInfo\Application;
use OCA\NewsLetterBuilder\Service\NewsLetterService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\Mail\IMailer;

class NewsLetterApiController extends ApiController {
	private IMailer $mailer;
	private NewsLetterService $service;
	private ?string $userId;

	use Errors;

	public function __construct(IRequest $request,
								NewsLetterService $service,
								IMailer $mailer,
								?string $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
		$this->mailer = $mailer;
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function index(): DataResponse {
		return new DataResponse($this->service->findAll($this->userId));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function create(string $title, string $content): DataResponse {
		return new DataResponse($this->service->create($title, $content,
			$this->userId));
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function update(int $id, string $title,
						   string $content): DataResponse {
		return $this->handleNotFound(function () use ($id, $title, $content) {
			return $this->service->update($id, $title, $content, $this->userId);
		});
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->userId);
		});
	}


	 /**
	 * @NoAdminRequired
	 */
	public function sendEmail(string $subject,string $content , string $mailsto) {

  	
		$message =  $this->mailer->createMessage();
		$message->setSubject($subject);
		$message->setFrom(array('notification@cacem.fr' => 'Fiche Navette Notifier'));
		$message->setTo(explode(',',$mailsto)); //array('herve.dechavigny@cacem.fr','sebastien.kneur@cacem.fr')
		$message->setBody('test', 'text/html');//$content
		$this->mailer->send($message);
  
	  }
}
