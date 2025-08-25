<?php

namespace Phrity\DevKit;

use Composer\InstalledVersions;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{
    InputInterface,
    InputOption,
};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallCommand extends Command
{
    private FileHandler $fileHandler;
    private SymfonyStyle|null $style = null;

    public function __construct()
    {
        $this->fileHandler = new FileHandler();
        parent::__construct('install');
    }

    protected function configure(): void
    {
        $this->setDescription('Install standard resources.');
        $this->addOption('target', null, InputOption::VALUE_OPTIONAL, 'Install target');
        $this->addOption('repo.name', null, InputOption::VALUE_OPTIONAL, 'Repo name');
        $this->addOption('repo.uri', null, InputOption::VALUE_OPTIONAL, 'Repo URI');
        $this->addOption('repo.page', null, InputOption::VALUE_OPTIONAL, 'Repo page');
        $this->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Namespace');
        $this->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Name');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->style = (new SymfonyStyle($input, $output))->getErrorStyle();

        $source = realpath(__DIR__ . '/../');
        $target = $this->directory($input->getOption('target') ?? '.');
        $repoName = $input->getOption('repo.name') ?? $this->repoName($target);
        $repoUri = $input->getOption('repo.uri') ?? $this->repoUri($target);
        $repoPage = $input->getOption('repo.page') ?? $this->repoPage($target);
        $name = $input->getOption('name') ?? $this->name($target);
        $namespace = $input->getOption('namespace') ?? $this->namespace($target);
        $this->style()->text("Installing as <fg=green>{$repoName}</> in <fg=green>{$target}</>");

        $this->directory("{$target}/docs");
        $this->directory("{$target}/src");
        $this->directory("{$target}/tests/suites");
        $this->copy("{$source}/.gitattributes", "{$target}/.gitattributes");
        $this->copy("{$source}/.gitattributes", "{$target}/.gitattributes");
        $this->copy("{$source}/.github/workflows/acceptance.yml", "{$target}/.github/workflows/acceptance.yml");
        $this->copy("{$source}/.gitignore", "{$target}/.gitignore");
        $this->copy("{$source}/Makefile", "{$target}/Makefile");
        $this->copy("{$source}/phpcs.xml", "{$target}/phpcs.xml");
        $this->copy("{$source}/phpstan.neon", "{$target}/phpstan.neon");
        $this->copy("{$source}/phpunit.xml", "{$target}/phpunit.xml");
        $this->copy("{$source}/tests/bootstrap.php", "{$target}/tests/bootstrap.php");
        $this->template("{$source}/templates/composer.json", "{$target}/composer.json", [
            'repo.name' => $repoName,
            'repo.page' => $repoPage,
            'namespace' => $namespace,
        ]);
        $this->template("{$source}/templates/MIT-LICENSE", "{$target}/LICENSE", [
            'year' => date('Y'),
        ]);
        $this->template("{$source}/templates/docu.json", "{$target}/docu.json", []);
        $this->template("{$source}/templates/README.md", "{$target}/README.md", [
            'repo.name' => $repoName,
            'repo.uri' => $repoUri,
            'name' => $name,
        ]);
        return 0;
    }

    private function style(): SymfonyStyle
    {
        /** @var SymfonyStyle */
        return $this->style;
    }

    private function repoName(string $path): string
    {
        return strtolower(implode('/', explode('-', basename($path), 2)));
    }

    private function repoPage(string $path): string
    {
        return strtolower(implode('-', array_slice(explode('-', basename($path)), 1)));
    }

    private function repoUri(string $path): string
    {
        return strtolower(basename($path));
    }

    private function namespace(string $path): string
    {
        return implode('\\\\', explode('-', ucwords(strtolower(basename($path)), '-'))) . '\\\\';
    }

    private function name(string $path): string
    {
        return implode(' ', explode('-', ucwords(strtolower(basename($path)), '-')));
    }

    /**
     * @param array<string,string> $replacers
     */
    private function template(string $source, string $target, array $replacers): void
    {
        $target = $this->fileHandler->template($source, $target, $replacers);
        $this->style()->text("Created <fg=green>{$target}</> from template using " . json_encode($replacers));
    }

    private function copy(string $source, string $target): void
    {
        $target = $this->fileHandler->copy($source, $target);
        $this->style()->text("Copied <fg=green>{$source}</> to <fg=green>{$target}</>");
    }

    private function directory(string $target): string
    {
        if (!$this->fileHandler->isDirectory($target)) {
            $target = $this->fileHandler->directory($target);
            $this->style()->text(" > Created directory <fg=green>{$target}/</>");
        }
        return realpath($target) ?: $target;
    }
}
