<?php

namespace App\Base;

use App\Base\HookFulfillers\HttpFulfillers\OnAfterResponseDecidedFulfillers\BaseAfterResponseDecided;
use App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers\BypassKey;
use App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers\BaseContext as HttpBaseContext;
use App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers\NormalizeSession;
use App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers\RedirectSlash;
use App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers\DecideUserDetails;
use App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers\Tailwindcss;
use App\Base\HookFulfillers\HttpFulfillers\OnContestResponseFulfillers\BaseResponse;
use App\Base\HookFulfillers\HttpFulfillers\OnNotFoundFulfillers\BaseNotFound;
use App\Base\HookFulfillers\SystemcallFulfillers\OnAfterControllerRunFulfillers\BaseAfterControllerRun;
use App\Base\HookFulfillers\SystemcallFulfillers\OnAfterControllerRunFulfillers\LogFailures;
use App\Base\HookFulfillers\SystemcallFulfillers\OnAfterControllerRunFulfillers\NoteIfFailed;
use App\Base\HookFulfillers\SystemcallFulfillers\OnContestContextFulfillers\BaseContext as SystemcallBaseContext;
use App\Base\Services\TailwindcssManager;
use App\Lib\TemplateMaster\ReactIslandExtension;
use App\Lib\TemplateMaster\TemplateMaster;
use App\Lib\TemplateMaster\TranslationExtension;
use App\Lib\TemplateMaster\Twig\BetterTwig;
use App\Lib\TemplateMaster\Twig\FakeTemplateLoader;
use App\Lib\TemplateMaster\Twig\WindowsSafeFilesystemCache;
use Hizech\Bliss\Email\EmailProvider\Cases\Mailhog;
use Hizech\Bliss\Email\EmailProvider\Cases\Native;
use Hizech\Bliss\Email\EmailProvider\EmailProvider;
use Hizech\Bliss\Misc\Util;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class App extends \Hizech\Bliss\App\App
{
    /**
     * @var array<string, mixed> $config
     */
    private array $config;

    private TemplateMaster $template_master;
    private TailwindcssManager $tailwindcss_manager;
    private SessionHandlerInterface $session_handler;

    /**
     * @return array<string, mixed>
     */
    public function getConfig() : array {

        if(isset($this->config)) return $this->config;

        else {

            $config = [];

            $config['migrations'] = require($this->getRootPath() . I . 'app' . I . 'config' . I . 'migrations.php');
            $config['inputs'] = require($this->getRootPath() . I . 'app' . I . 'config' . I . 'inputs.php');
            $config['app'] = require($this->getRootPath() . I . 'app' . I . 'config' . I . 'app.php');

            $this->config = $config;
            return $this->config;

        }

    }

    public function getEmailProvider() : EmailProvider {

        if($this->getEnv()['APP_DEVELOPMENT']) {

            $mailhog = new Mailhog(

                $this->getEnv()['MAILHOG_HOST'],
                $this->getEnv()['MAILHOG_PORT'],

            );

            return $mailhog;
        }
        else {
            $native = new Native();
            return $native;
        }

    }

    public function getTailwindcssManager() : TailwindcssManager {
        if (isset($this->tailwindcss_manager)) {
            return $this->tailwindcss_manager;
        }

        $manager = new \App\Lib\TailwindcssManager\TailwindcssManager($this->getTailwindcssStoragePath());

        $this->tailwindcss_manager = $manager;
        return $this->tailwindcss_manager;
    }

    public function getTemplateMaster(): TemplateMaster
    {
        if (isset($this->template_master)) {
            return $this->template_master;
        }

        $env = $this->getEnv();
        $is_dev = $env['APP_DEVELOPMENT'];

        $array_loader = new ArrayLoader();
        $fake_loader = new FakeTemplateLoader();

        $loader = new ChainLoader([
            new FilesystemLoader(Util::joinPath($this->getRootPath(), 'app', 'twig')),
            $array_loader,
            $fake_loader,
        ]);

        $twig_cache_path = Util::joinPath($this->getStoragePath(), 'twig', 'cache');
        $cache_reporter_path = Util::joinPath($this->getStoragePath(), 'twig', 'auto-template-cache', 'cache_reporter.php');

        $twig = new BetterTwig($loader, [
            'cache' => $is_dev ? false : new WindowsSafeFilesystemCache($twig_cache_path),
            'auto_reload' => $is_dev,
            'debug' => $is_dev,
        ]);

        $twig->addExtension(new TranslationExtension($this->getTranslator()));
        $twig->addExtension(new ReactIslandExtension());

        $this->template_master = new TemplateMaster(
            $twig,
            $array_loader,
            $fake_loader,
            $cache_reporter_path,
            $is_dev,
        );

        $twig->addGlobal('app', $this);

        return $this->template_master;
    }

    public function getFrontApproachesPath(): string
    {
        return $this->getRootPath() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'front_approaches.php';
    }

    public function getStorageCacheFolders(): array
    {
        $new_folders = [
            'twig/cache',
            'twig/auto-template-cache',
        ];

        return array_merge(parent::getStorageCacheFolders(), $new_folders);
    }

    public function purgeCache(): void
    {
        parent::purgeCache();

        $twig_cache = Util::joinPath($this->getStoragePath(), 'twig', 'cache');
        if (is_dir($twig_cache)) {
            self::deleteDirectoryContents($twig_cache);
        }
    }

    public function rebuildCache(): void
    {
        parent::rebuildCache();
        unset($this->template_master);
        $this->getTemplateMaster();
    }

    private static function deleteDirectoryContents(string $dir): void
    {
        $items = glob($dir . DIRECTORY_SEPARATOR . '*');
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if (is_dir($item)) {
                self::deleteDirectoryContents($item);
                rmdir($item);
            } else {
                unlink($item);
            }
        }
    }

    public function getStorageFolders(): array
    {
        $new_folders = [
            'twig/cache',
            'twig/auto-template-cache',
            'tailwindcss',
        ];

        return array_merge(parent::getStorageFolders(), $new_folders);
    }

    public function getTailwindcssStoragePath(): string
    {
        return Util::joinPath($this->getStoragePath(), 'tailwindcss');
    }


    protected function onHttpContestContextFulfillers(): array
    {
        $ar = [
            'BaseContext' => new HttpBaseContext($this),
            'RedirectSlash' => new RedirectSlash($this),
            'NormalizeSession' => new NormalizeSession($this),
            'BypassKey' => new BypassKey($this),
            'RateLimit' => new HookFulfillers\HttpFulfillers\OnContestContextFulfillers\RateLimit($this),
            'DecideUserDetails' => new DecideUserDetails($this),
            'Tailwindcss' => new Tailwindcss($this),
        ];
        return array_merge(parent::onHttpContestContextFulfillers(), $ar);
    }

    protected function onHttpContestResponseFulfillers(): array
    {
        $ar = [
            'BaseResponse' => new BaseResponse($this),
        ];
        return array_merge(parent::onHttpContestResponseFulfillers(), $ar);
    }

    protected function onHttpNotFoundFulfillers(): array
    {
        $ar = [
            'BaseNotFound' => new BaseNotFound($this),
        ];
        return array_merge(parent::onHttpNotFoundFulfillers(), $ar);
    }

    protected function onHttpAfterResponseDecidedFulfillers(): array
    {
        $ar = [
            'BaseAfterResponseDecided' => new BaseAfterResponseDecided($this),
        ];
        return array_merge(parent::onHttpAfterResponseDecidedFulfillers(), $ar);
    }

    protected function onSystemcallContestContextFulfillers(): array
    {
        $ar = [
            'BaseContext' => new SystemcallBaseContext($this),
        ];
        return array_merge(parent::onSystemcallContestContextFulfillers(), $ar);
    }


    protected function onSystemcallNotFoundFulfillers(): array
    {
        $ar = [
            'BaseNotFound' => new HookFulfillers\SystemcallFulfillers\OnNotFoundFulfillers\BaseNotFound($this),
        ];
        return array_merge(parent::onSystemcallNotFoundFulfillers(), $ar);
    }

    protected function onSystemcallAfterControllerRunFulfillers(): array
    {
        $ar = [
            
            'BaseAfterControllerRun' => new BaseAfterControllerRun($this),
            'NoteIfFailed' => new NoteIfFailed($this),
            'LogFailures' => new LogFailures($this),
            
        ];
        return array_merge(parent::onSystemcallAfterControllerRunFulfillers(), $ar);
    }

    public function getSessionHandler() : SessionHandlerInterface {

        if(isset($this->session_handler)) return $this->session_handler;
        else {

            $this->session_handler = new NativeFileSessionHandler();

            return $this->session_handler;
        }
    }

}
