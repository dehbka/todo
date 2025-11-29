<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->resetDatabase();
    }

    protected function em(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        return $em;
    }

    private function resetDatabase(): void
    {
        $em = $this->em();
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        if ($metadata === []) {
            return;
        }

        $tool = new SchemaTool($em);
        // Drop and recreate schema to ensure isolation
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }

    /**
     * Convenience to decode JSON responses.
     * @return array<mixed>
     */
    protected function json(): array
    {
        $content = $this->client->getResponse()->getContent();
        self::assertNotFalse($content, 'Expected a response body');
        /** @var array<mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $data;
    }
}
