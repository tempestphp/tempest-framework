<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Vite;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Vite\Manifest\Manifest;
use Tempest\Vite\PrefetchConfig;
use Tempest\Vite\PrefetchStrategy;
use Tempest\Vite\TagCompiler\TagCompiler;
use Tempest\Vite\TagsResolver\ManifestTagsResolver;
use Tempest\Vite\ViteConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ManifestTagsResolverTest extends FrameworkIntegrationTestCase
{
    use HasFixtures;

    public function test_resolve_script(): void
    {
        $resolver = new ManifestTagsResolver(
            viteConfig: $this->container->get(ViteConfig::class),
            tagCompiler: $this->container->get(TagCompiler::class),
            manifest: Manifest::fromArray($this->fixture('simple-manifest.json')),
        );

        $this->assertSame(
            expected: [
                '<script type="module" src="/build/assets/main-YJD4Cw3J.js"></script>',
            ],
            actual: $resolver->resolveTags(['src/main.ts']),
        );
    }

    public function test_resolve_script_with_css(): void
    {
        $resolver = new ManifestTagsResolver(
            viteConfig: $this->container->get(ViteConfig::class),
            tagCompiler: $this->container->get(TagCompiler::class),
            manifest: Manifest::fromArray($this->fixture('simple-manifest-with-css.json')),
        );

        $this->assertSame(
            expected: [
                '<link rel="stylesheet" href="/build/assets/main-DObprJ9K.css" />',
                '<script type="module" src="/build/assets/main-CK61jJwL.js"></script>',
            ],
            actual: $resolver->resolveTags(['src/main.ts']),
        );
    }

    #[TestWith([PrefetchStrategy::WATERFALL])]
    #[TestWith([PrefetchStrategy::AGGRESSIVE])]
    #[TestWith([PrefetchStrategy::NONE])]
    public function test_resolve_script_with_prefetching(PrefetchStrategy $strategy): void
    {
        $this->container->config(new ViteConfig(
            nonce: '123',
            prefetching: new PrefetchConfig(
                strategy: $strategy,
            ),
        ));

        $resolver = new ManifestTagsResolver(
            viteConfig: $this->container->get(ViteConfig::class),
            tagCompiler: $this->container->get(TagCompiler::class),
            manifest: Manifest::fromArray($this->fixture('prefetching-manifest.json')),
        );

        $tags = $resolver->resolveTags(['resources/js/app.js']);

        $this->assertContains('<link rel="modulepreload" href="/build/assets/index-BSdK3M0e.js" nonce="123" />', $tags);
        $this->assertContains('<link rel="stylesheet" href="/build/assets/index-B3s1tYeC.css" nonce="123" />', $tags);
        $this->assertContains('<script type="module" src="/build/assets/app-lliD09ip.js" nonce="123"></script>', $tags);

        if ($strategy !== PrefetchStrategy::NONE) {
            $this->assertStringContainsString('nonce="123', $tags[3]);
            $this->assertStringContainsString(
                needle: <<<JS
                [{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/index-B3s1tYeC.css"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/index-BSdK3M0e.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/_plugin-vue_export-helper-DlAUqK2U.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/ApplicationLogo-BhIZH06z.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/GuestLayout-BY3LC-73.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/TextInput-C8CCB_U_.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/PrimaryButton-DuXwr-9M.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/ConfirmPassword-CDwcgU8E.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/ForgotPassword-B0WWE0BO.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/Login-DAFSdGSW.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/Register-CfYQbTlA.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/ResetPassword-BNl7a4X1.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/VerifyEmail-CyukB_SZ.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/AuthenticatedLayout-DfWF52N1.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/Dashboard-DM_LxQy2.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/DeleteUserForm-B1oHFaVP.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/UpdatePasswordForm-CaeWqGla.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/UpdateProfileInformationForm-CJwkYwQQ.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/Edit-CYV2sXpe.js"},{"rel":"prefetch","fetchpriority":"low","href":"/build/assets/Welcome-D_7l79PQ.js"}]
                JS,
                haystack: $tags[3],
            );
        }
    }
}
