<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\AccessControl;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AccessControl\AccessControl;
use Tempest\Auth\AccessControl\AccessDecision;
use Tempest\Auth\AccessControl\Policy;
use Tempest\Auth\AccessControl\PolicyBasedAccessControl;
use Tempest\Auth\AuthConfig;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Authentication\AuthenticatorInitializer;
use Tempest\Auth\Authentication\CanAuthenticate;
use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Auth\Exceptions\NoPolicyWereFoundForResource;
use Tempest\Database\PrimaryKey;
use Tests\Tempest\Integration\Auth\Fixtures\InMemoryAuthenticatorInitializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use UnitEnum;

final class PolicyBasedAccessControlTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function returns_policy_based_access_control_instance_by_default(): void
    {
        $this->assertInstanceOf(PolicyBasedAccessControl::class, $this->container->get(AccessControl::class));
    }

    #[Test]
    public function can_grant_access_when_policy_returns_true(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted('view', $post, $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function can_deny_access_when_policy_returns_false(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 2); // Different user

        $result = $accessControl->isGranted('edit', $post, $user);

        $this->assertFalse($result->granted);
    }

    #[Test]
    public function can_grant_access_when_policy_returns_access_decision(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted('delete', $post, $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function can_deny_access_when_policy_returns_denied_access_decision(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 2); // Different user

        $result = $accessControl->isGranted('delete', $post, $user);

        $this->assertFalse($result->granted);
        $this->assertEquals('Only the author can delete their post', $result->message);
    }

    #[Test]
    public function can_work_with_enum_actions(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted(PostAction::VIEW, $post, $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function can_work_with_class_strings(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted('create', Post::class, $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function uses_current_authenticated_user_when_no_subject_provided(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $this->container->removeInitializer(AuthenticatorInitializer::class);
        $this->container->addInitializer(InMemoryAuthenticatorInitializer::class);

        $user = new User(userId: 1);
        $authenticator = $this->container->get(Authenticator::class);
        $authenticator->authenticate($user);

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);

        $this->assertTrue($accessControl->isGranted('edit', $post)->granted);
    }

    #[Test]
    public function handles_no_authenticated_model_gracefully(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);

        $this->assertFalse($accessControl->isGranted('edit', $post)->granted);
    }

    #[Test]
    public function throws_exception_when_no_policies_found(): void
    {
        $this->container->config(new AuthConfig(policies: []));

        $accessControl = $this->container->get(AccessControl::class);
        $comment = new Comment(content: 'Test comment');

        $this->expectException(NoPolicyWereFoundForResource::class);
        $this->expectExceptionMessage('No policies were found for resource `Tests\Tempest\Integration\Auth\AccessControl\Comment`.');

        $accessControl->isGranted('view', $comment);
    }

    #[Test]
    public function deny_access_unless_granted_throws_when_access_denied(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 2); // Different user

        $this->expectException(AccessWasDenied::class);
        $this->expectExceptionMessage('Only the author can delete their post');

        $accessControl->denyAccessUnlessGranted('delete', $post, $user);
    }

    #[Test]
    public function deny_access_unless_granted_passes_when_access_granted(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 1);

        // Should not throw
        $accessControl->denyAccessUnlessGranted('view', $post, $user);

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function multiple_policies_all_must_grant_access(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class, UserPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted('manage', resource: $user, subject: $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function multiple_policies_any_denial_blocks_access(): void
    {
        $this->container->config(new AuthConfig(policies: [PostPolicy::class, UserPolicy::class]));

        $accessControl = $this->container->get(AccessControl::class);
        $user = new User(userId: 1);
        $otherUser = new User(userId: 2);

        $result = $accessControl->isGranted('manage', resource: $user, subject: $otherUser);

        $this->assertFalse($result->granted);
    }
}

enum PostAction: string
{
    case VIEW = 'view';
    case EDIT = 'edit';
    case DELETE = 'delete';
}

final class Post
{
    public PrimaryKey $id;

    public function __construct(
        public string $title,
        public int $authorId,
    ) {}
}

final class User implements CanAuthenticate
{
    public PrimaryKey $id;

    public function __construct(
        int $userId,
    ) {
        $this->id = new PrimaryKey($userId);
    }
}

final class Comment
{
    public function __construct(
        public string $content,
    ) {}
}

final class PostPolicy implements Policy
{
    public string $model = Post::class;

    /** @param null|Post $resource */
    public function check(UnitEnum|string $action, ?object $resource, ?object $subject): bool|AccessDecision
    {
        if (! ($subject instanceof User)) {
            return false;
        }

        return match ($action) {
            PostAction::VIEW, 'view' => true, // Anyone can view
            PostAction::EDIT, 'edit' => $resource?->authorId === $subject->id->value,
            PostAction::DELETE, 'delete' => $resource?->authorId === $subject->id->value
                ? AccessDecision::granted()
                : AccessDecision::denied('Only the author can delete their post'),
            'create' => true,
            default => false,
        };
    }
}

final class UserPolicy implements Policy
{
    public string $model = User::class;

    /** @param null|User $resource */
    public function check(UnitEnum|string $action, ?object $resource, ?object $subject): bool|AccessDecision
    {
        if (! ($subject instanceof User)) {
            return false;
        }

        return match ($action) {
            'manage' => $resource?->id->value === $subject->id->value,
            default => false,
        };
    }
}
