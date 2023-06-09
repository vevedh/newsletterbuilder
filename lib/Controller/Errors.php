<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\NewsLetterBuilder\Controller;

use Closure;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\NewsLetterBuilder\Service\NewsLetterNotFound;

trait Errors {
	protected function handleNotFound(Closure $callback): DataResponse {
		try {
			return new DataResponse($callback());
		} catch (NewsLetterNotFound $e) {
			$message = ['message' => $e->getMessage()];
			return new DataResponse($message, Http::STATUS_NOT_FOUND);
		}
	}
}
