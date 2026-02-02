<?php

namespace Tests\Phpunit;

use App\Lib\TailwindcssManager\TailwindcssManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TailwindcssManagerTest extends TestCase
{
    private string $storage_path;
    private string $temp_dir;

    protected function setUp(): void
    {
        $this->temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tailwind_test_' . uniqid();
        mkdir($this->temp_dir, 0755, true);

        $this->storage_path = $this->temp_dir . DIRECTORY_SEPARATOR . 'storage';
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->temp_dir);
    }

    public function testRegisterPersistsToStorage(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';
        file_put_contents($input, 'body {}');

        $manager->register($input, $output, []);

        $registry_file = $this->storage_path . DIRECTORY_SEPARATOR . 'registry.json';
        $this->assertFileExists($registry_file);

        $data = json_decode((string) file_get_contents($registry_file), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey($input, $data);
        $this->assertSame($output, $data[$input]['output_path']);
    }

    public function testBuildThrowsForUnregisteredInput(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not registered');

        $manager->build('/nonexistent/input.css');
    }

    public function testIsOutputFreshThrowsForUnregisteredInput(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not registered');

        $manager->isOutputFresh('/nonexistent/input.css');
    }

    public function testIsOutputFreshReturnsFalseWhenOutputMissing(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';
        file_put_contents($input, 'body {}');

        $manager->register($input, $output, []);

        $this->assertFalse($manager->isOutputFresh($input));
    }

    public function testIsOutputFreshReturnsFalseWithNoSnapshot(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';
        file_put_contents($input, 'body {}');
        file_put_contents($output, 'body {}');

        $manager->register($input, $output, []);

        $this->assertFalse($manager->isOutputFresh($input));
    }

    public function testIsOutputFreshDetectsWatchedFileChange(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';
        $template = $this->temp_dir . DIRECTORY_SEPARATOR . 'page.twig';

        file_put_contents($input, 'body {}');
        file_put_contents($output, 'body {}');
        file_put_contents($template, '<div class="flex"></div>');

        $manager->register($input, $output, [$template]);

        // Simulate a snapshot by writing it manually
        $this->saveManualSnapshot($manager, $input, [$input, $template]);

        $this->assertTrue($manager->isOutputFresh($input));

        // Touch the template to simulate a change
        sleep(1);
        touch($template);
        clearstatcache();

        $this->assertFalse($manager->isOutputFresh($input));
    }

    public function testIsOutputFreshDetectsInputFileChange(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';

        file_put_contents($input, 'body {}');
        file_put_contents($output, 'body {}');

        $manager->register($input, $output, []);

        $this->saveManualSnapshot($manager, $input, [$input]);

        $this->assertTrue($manager->isOutputFresh($input));

        sleep(1);
        touch($input);
        clearstatcache();

        $this->assertFalse($manager->isOutputFresh($input));
    }

    public function testGlobWatchPathsResolveCorrectly(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $twig_dir = $this->temp_dir . DIRECTORY_SEPARATOR . 'twig';
        mkdir($twig_dir, 0755, true);

        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';
        $template_a = $twig_dir . DIRECTORY_SEPARATOR . 'a.twig';
        $template_b = $twig_dir . DIRECTORY_SEPARATOR . 'b.twig';

        file_put_contents($input, 'body {}');
        file_put_contents($output, 'body {}');
        file_put_contents($template_a, '<div></div>');
        file_put_contents($template_b, '<span></span>');

        $glob_pattern = $twig_dir . DIRECTORY_SEPARATOR . '*.twig';
        $manager->register($input, $output, [$glob_pattern]);

        // Snapshot with all resolved files
        $this->saveManualSnapshot($manager, $input, [$input, $template_a, $template_b]);

        $this->assertTrue($manager->isOutputFresh($input));

        // Touch one template
        sleep(1);
        touch($template_b);
        clearstatcache();

        $this->assertFalse($manager->isOutputFresh($input));
    }

    public function testGlobDetectsNewFile(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $twig_dir = $this->temp_dir . DIRECTORY_SEPARATOR . 'twig';
        mkdir($twig_dir, 0755, true);

        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';
        $template_a = $twig_dir . DIRECTORY_SEPARATOR . 'a.twig';

        file_put_contents($input, 'body {}');
        file_put_contents($output, 'body {}');
        file_put_contents($template_a, '<div></div>');

        $glob_pattern = $twig_dir . DIRECTORY_SEPARATOR . '*.twig';
        $manager->register($input, $output, [$glob_pattern]);

        $this->saveManualSnapshot($manager, $input, [$input, $template_a]);

        $this->assertTrue($manager->isOutputFresh($input));

        // Add a new template — glob now resolves more files than the snapshot
        file_put_contents($twig_dir . DIRECTORY_SEPARATOR . 'b.twig', '<span></span>');
        clearstatcache();

        $this->assertFalse($manager->isOutputFresh($input));
    }

    public function testGlobDetectsDeletedFile(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $twig_dir = $this->temp_dir . DIRECTORY_SEPARATOR . 'twig';
        mkdir($twig_dir, 0755, true);

        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';
        $template_a = $twig_dir . DIRECTORY_SEPARATOR . 'a.twig';
        $template_b = $twig_dir . DIRECTORY_SEPARATOR . 'b.twig';

        file_put_contents($input, 'body {}');
        file_put_contents($output, 'body {}');
        file_put_contents($template_a, '<div></div>');
        file_put_contents($template_b, '<span></span>');

        $glob_pattern = $twig_dir . DIRECTORY_SEPARATOR . '*.twig';
        $manager->register($input, $output, [$glob_pattern]);

        $this->saveManualSnapshot($manager, $input, [$input, $template_a, $template_b]);

        $this->assertTrue($manager->isOutputFresh($input));

        // Delete a template — snapshot has a file that no longer exists
        unlink($template_b);
        clearstatcache();

        $this->assertFalse($manager->isOutputFresh($input));
    }

    public function testRegisterIsIdempotent(): void
    {
        $manager = new TailwindcssManager($this->storage_path);

        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';
        file_put_contents($input, 'body {}');

        $manager->register($input, $output, []);
        $mtime_first = filemtime($this->storage_path . DIRECTORY_SEPARATOR . 'registry.json');

        sleep(1);
        $manager->register($input, $output, []);
        clearstatcache();
        $mtime_second = filemtime($this->storage_path . DIRECTORY_SEPARATOR . 'registry.json');

        $this->assertSame($mtime_first, $mtime_second);
    }

    public function testRegistrySurvivesNewInstance(): void
    {
        $input = $this->temp_dir . DIRECTORY_SEPARATOR . 'input.css';
        $output = $this->temp_dir . DIRECTORY_SEPARATOR . 'output.css';
        file_put_contents($input, 'body {}');

        $manager1 = new TailwindcssManager($this->storage_path);
        $manager1->register($input, $output, []);

        // New instance reads from disk
        $manager2 = new TailwindcssManager($this->storage_path);
        file_put_contents($output, 'body {}');

        // Should not throw — registration persisted
        $this->assertFalse($manager2->isOutputFresh($input));
    }

    /**
     * Write a snapshot file directly to simulate a post-build state.
     * @param list<string> $paths
     */
    private function saveManualSnapshot(TailwindcssManager $manager, string $input_path, array $paths): void
    {
        $mtimes = [];
        foreach ($paths as $path) {
            $mtimes[$path] = filemtime($path);
        }

        $snapshot_file = $this->storage_path . DIRECTORY_SEPARATOR . 'snapshot_' . md5($input_path) . '.json';
        file_put_contents($snapshot_file, json_encode($mtimes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = glob($dir . DIRECTORY_SEPARATOR . '*');
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if (is_dir($item)) {
                $this->deleteDir($item);
            } else {
                unlink($item);
            }
        }

        rmdir($dir);
    }
}
