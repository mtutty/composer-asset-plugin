<?php

/*
 * This file is part of the Fxp Composer Asset Plugin package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Composer\AssetPlugin\Repository\Vcs;

use Composer\Cache;
use Composer\Json\JsonFile;
use Composer\Repository\Vcs\GitDriver as BaseGitDriver;

/**
 * Git vcs driver.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class GitDriver extends BaseGitDriver
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * {@inheritDoc}
     */
    public function getComposerInformation($identifier)
    {
        $this->infoCache[$identifier] = Util::readCache($this->infoCache, $this->cache, $this->repoConfig['asset-type'], $identifier);

        if (!isset($this->infoCache[$identifier])) {
            $resource = sprintf('%s:%s', escapeshellarg($identifier), $this->repoConfig['filename']);
            $this->process->execute(sprintf('git show %s', $resource), $composer, $this->repoDir);

            if (!trim($composer)) {
                $composer = array('_nonexistent_package' => true);
            } else {
                $composer = JsonFile::parseJson($composer, $resource);
                $composer = Util::addComposerTimeProcessor($composer, $this->process, sprintf('git log -1 --format=%%at %s', escapeshellarg($identifier)), $this->repoDir, '@');
            }

            Util::writeCache($this->cache, $this->repoConfig['asset-type'], $identifier, $composer);
            $this->infoCache[$identifier] = $composer;
        }

        return $this->infoCache[$identifier];
    }
}
