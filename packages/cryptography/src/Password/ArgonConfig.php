<?php

namespace Tempest\Cryptography\Password;

final class ArgonConfig implements PasswordHashingConfig
{
    public HashingAlgorithm $algorithm = HashingAlgorithm::ARGON2ID;

    public array $options {
        get => [
            'memory_cost' => $this->memoryCost,
            'time_cost' => $this->timeCost,
            'threads' => $this->threads,
        ];
    }

    /**
     * @param int $memoryCost The amount of memory in bytes that Argon will use while trying to compute a hash. The higher, the more resistant to GPU/ASIC attacks, but also more resource-intensive and slow.
     * @param int $timeCost Number of passes Argon will perform over the memory. Increasing this increases the computation time but makes brute-force attacks slower.
     * @param int $threads Number of threads used to compute the hash. More threads can improve performance on multi-core CPUs by parallelizing the computation, but may impact other processes.
     */
    public function __construct(
        public int $memoryCost = PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
        public int $timeCost = PASSWORD_ARGON2_DEFAULT_TIME_COST,
        public int $threads = PASSWORD_ARGON2_DEFAULT_THREADS,
    ) {}
}
