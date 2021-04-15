<?php

declare(strict_types=1);

namespace Library\SharedKernel\Infrastructure\Controller;

use Exception;
use JMS\Serializer\SerializerInterface;
use JsonException;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @package App\Shared\Infrastructure
 */
trait ApiControllerTrait
{
    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param object $contract
     */
    protected function loadDataAndValidateRequest(Request $request, object &$contract): void
    {
        $files = [];
        $contentTypeHeaderValue = $request->headers->get('Content-Type');

        /**
         * Header X-Symfony-Test is checked because native symfony http client don't support
         * request headers override
         */
        if (
            $contentTypeHeaderValue !== null &&
            (
                $request->headers->get('X-Symfony-Test') !== null ||
                str_starts_with($contentTypeHeaderValue, 'multipart/form-data')
            )
        ) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $fileUrls */
            $files = $request->files->all();
            $all = $request->request->all();

            try {
                $data = json_encode($this->ensureBooleanType($all), JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new LogicException($e->getMessage());
            }
        } else {
            $data = $request->getContent();
        }

        if (!is_string($data) || $data === "") {
            throw $this->createMalformedRequestException();
        }

        try {
            $contract = $this->serializer->deserialize(
                $data,
                $contract::class,
                'json'
            );
        } catch (Exception $exception) {
            throw new NotAcceptableHttpException(
                'Some properties may have wrong types',
                $exception,
                Response::HTTP_NOT_ACCEPTABLE,
            );
        }

        foreach ($files as $key => $file) {
            $contract->$key = $file;
        }

        $errors = $this->validator->validate($contract);

        if ($errors->count() > 0) {
            throw new BadRequestHttpException(
                $this->createErrorMessage($errors)
            );
        }
    }

    private function createMalformedRequestException(): BadRequestHttpException
    {
        return new BadRequestHttpException("Request content is malformed");
    }

    /**
     * @param \Symfony\Component\Validator\ConstraintViolationListInterface $violations
     * @return string
     */
    private function createErrorMessage(ConstraintViolationListInterface $violations): string
    {
        $errors = [];

        /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        try {
            return json_encode(['errors' => $errors], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new LogicException($e->getMessage());
        }
    }

    /**
     * @param array $elements
     * @return array<string, mixed>
     */
    private function ensureBooleanType(array $elements): array
    {
        foreach ($elements as $index => $value) {
            if (str_starts_with($index, 'is')) {
                $elements[$index] = !($value === "false");
            }
        }

        return $elements;
    }
}
