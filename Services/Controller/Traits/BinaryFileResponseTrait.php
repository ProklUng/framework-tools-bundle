<?php

namespace Prokl\FrameworkExtensionBundle\Services\Controller\Traits;

use InvalidArgumentException;
use LogicException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;

/**
 * Trait BinaryFileResponseTrait
 * @package Prokl\FrameworkExtensionBundle\Services\Controller\Traits
 *
 * @since 01.07.2021
 */
trait BinaryFileResponseTrait
{
    /**
     * returnFile
     *
     * @param string $file Файл.
     *
     * @return BinaryFileResponse
     *
     * @throws InvalidArgumentException | LogicException
     */
    protected function returnFile(string $file) : BinaryFileResponse
    {
        $contentType = 'text/plain';

        if (class_exists(FileinfoMimeTypeGuesser::class)) {
            $mimeTypeGuesser = new FileinfoMimeTypeGuesser();
            if ($mimeTypeGuesser->isGuesserSupported()) {
                $contentType = $mimeTypeGuesser->guessMimeType($file) ?? $contentType;
            }
        }

        return new BinaryFileResponse(
            $file,
            Response::HTTP_CREATED,
            [
                'Content-Type' => $contentType,
            ],
            false,
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }
}
