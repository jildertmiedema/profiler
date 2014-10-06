<?php

/**
 * Profiler.php
 *
 * @author Dennis de Greef <github@link0.net>
 */
namespace Link0\Profiler;

/**
 * Profiler itself
 *
 * @package Link0\Profiler
 */
final class Profiler
{
    /**
     * @var ProfilerAdapterInterface $profilerAdapter
     */
    protected $profilerAdapter;

    /**
     * @var PersistenceService $persistenceService
     */
    protected $persistenceService;

    /**
     * @var ProfilerAdapterInterface[] $profilerAdapters
     */
    protected $preferredProfilerAdapters;

    /**
     * @param PersistenceHandlerInterface $persistenceHandler
     * @param int                         $flags
     * @param array                       $options
     */
    public function __construct(PersistenceHandlerInterface $persistenceHandler = null, $flags = 0, $options = array())
    {
        $this->preferredProfilerAdapters = array(
            new ProfilerAdapter\UprofilerAdapter($flags, $options),
            new ProfilerAdapter\XhprofAdapter($flags, $options),
        );
        $this->profilerAdapter = $this->getPreferredProfilerAdapter($flags, $options);
        $this->persistenceService = new PersistenceService($persistenceHandler);
    }

    /**
     * @param  ProfilerAdapterInterface $profilerAdapter
     * @return Profiler                 $this
     */
    public function setProfilerAdapter(ProfilerAdapterInterface $profilerAdapter)
    {
        $this->profilerAdapter = $profilerAdapter;

        return $this;
    }

    /**
     * @return ProfilerAdapterInterface $profilingAdapter
     */
    public function getProfilerAdapter()
    {
        return $this->profilerAdapter;
    }

    /**
     * @return PersistenceService $persistenceService
     */
    public function getPersistenceService()
    {
        return $this->persistenceService;
    }

    /**
     * @param  ProfilerAdapterInterface[] $preferredProfilerAdapters
     * @return Profiler                   $this
     */
    public function setPreferredProfilerAdapters($preferredProfilerAdapters)
    {
        $this->preferredProfilerAdapters = array();
        foreach ($preferredProfilerAdapters as $preferredProfilerAdapter) {
            if (in_array('Link0\Profiler\ProfilerAdapterInterface', class_implements($preferredProfilerAdapter))) {
                $this->preferredProfilerAdapters[] = $preferredProfilerAdapter;
            }
        }

        return $this;
    }

    /**
     * @return ProfilerAdapterInterface[] $preferredProfilerAdapters
     */
    public function getPreferredProfilerAdapters()
    {
        return $this->preferredProfilerAdapters;
    }

    /**
     * @throws Exception
     * @return ProfilerAdapterInterface $profilerAdapter
     */
    public function getPreferredProfilerAdapter()
    {
        /** @var ProfilerAdapterInterface $adapter */
        foreach ($this->getPreferredProfilerAdapters() as $adapter) {
            if ($adapter->isExtensionLoaded()) {
                return $adapter;
            }
        }

        throw new Exception("No valid profilerAdapter found. Did you forget to install an extension?");
    }

    /**
     * Starts profiling on the specific adapter
     *
     * @return Profiler $profiler
     */
    public function start()
    {
        $this->getProfilerAdapter()->start();

        return $this;
    }

    /**
     * @return boolean $isRunning Whether the profiler is currently running
     */
    public function isRunning()
    {
        return $this->getProfilerAdapter()->isRunning();
    }

    /**
     * Stops profiling and persists and returns the Profile object
     *
     * @return Profile
     */
    public function stop()
    {
        // Create a new profile based upon the data of the adapter
        $profile = new Profile();
        $profile->loadData($this->getProfilerAdapter()->stop());

        // Notify and persist the profile on the persistence service
        $this->getPersistenceService()->persist($profile);

        // Return the profile for further handling
        return $profile;
    }
}