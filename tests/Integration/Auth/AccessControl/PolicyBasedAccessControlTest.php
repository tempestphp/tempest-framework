<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\AccessControl;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AccessControl\AccessControl;
use Tempest\Auth\AccessControl\AccessDecision;
use Tempest\Auth\AccessControl\PolicyBasedAccessControl;
use Tempest\Auth\AccessControl\PolicyFor;
use Tempest\Auth\AuthConfig;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Authentication\AuthenticatorInitializer;
use Tempest\Auth\Authentication\CanAuthenticate;
use Tempest\Auth\Exceptions\AccessWasDenied;
use Tempest\Auth\Exceptions\NoPolicyWereFoundForResource;
use Tempest\Auth\Exceptions\PolicyMethodIsInvalid;
use Tempest\Database\PrimaryKey;
use Tests\Tempest\Integration\Auth\Fixtures\InMemoryAuthenticatorInitializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class PolicyBasedAccessControlTest extends FrameworkIntegrationTestCase
{
    use HasPolicyTests;

    #[Test]
    public function returns_policy_based_access_control_instance_by_default(): void
    {
        $this->assertInstanceOf(PolicyBasedAccessControl::class, $this->container->get(AccessControl::class));
    }

    #[Test]
    public function can_grant_access_when_policy_returns_true(): void
    {
        $this->registerPoliciesFrom(PostPolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted('view', $post, $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function can_deny_access_when_policy_returns_false(): void
    {
        $this->registerPoliciesFrom(PostPolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 2); // Different user

        $result = $accessControl->isGranted('edit', $post, $user);

        $this->assertFalse($result->granted);
    }

    #[Test]
    public function can_grant_access_when_policy_returns_access_decision(): void
    {
        $this->registerPoliciesFrom(PostPolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted('delete', $post, $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function can_deny_access_when_policy_returns_denied_access_decision(): void
    {
        $this->registerPoliciesFrom(PostPolicy::class);

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
        $this->registerPoliciesFrom(PostPolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted(PostAction::VIEW, $post, $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function can_work_with_class_strings(): void
    {
        $this->registerPoliciesFrom(PostPolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted('create', Post::class, $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function uses_current_authenticated_user_when_no_subject_provided(): void
    {
        $this->registerPoliciesFrom(PostPolicy::class);

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
        $this->registerPoliciesFrom(PostPolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);

        $this->assertFalse($accessControl->isGranted('edit', $post)->granted);
    }

    #[Test]
    public function throws_exception_when_no_policies_found(): void
    {
        $authConfig = new AuthConfig();
        $this->container->config($authConfig);

        $accessControl = $this->container->get(AccessControl::class);
        $comment = new Comment(content: 'Test comment');

        $this->expectException(NoPolicyWereFoundForResource::class);
        $this->expectExceptionMessage('No policies were found for resource `Tests\Tempest\Integration\Auth\AccessControl\Comment`.');

        $accessControl->isGranted('view', $comment);
    }

    #[Test]
    public function deny_access_unless_granted_throws_when_access_denied(): void
    {
        $this->registerPoliciesFrom(PostPolicy::class);

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
        $this->registerPoliciesFrom(PostPolicy::class);

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
        $this->registerPoliciesFrom(PostPolicy::class);
        $this->registerPoliciesFrom(UserPolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $user = new User(userId: 1);

        $result = $accessControl->isGranted('manage', resource: $user, subject: $user);

        $this->assertTrue($result->granted);
    }

    #[Test]
    public function multiple_policies_any_denial_blocks_access(): void
    {
        $this->registerPoliciesFrom(PostPolicy::class);
        $this->registerPoliciesFrom(UserPolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $user = new User(userId: 1);
        $otherUser = new User(userId: 2);

        $result = $accessControl->isGranted('manage', resource: $user, subject: $otherUser);

        $this->assertFalse($result->granted);
    }

    #[Test]
    public function policy_for_can_accept_multiple_actions(): void
    {
        $this->registerPoliciesFrom(MultiActionPolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $document = new Document(title: 'Test Document', authorId: 1);
        $author = new User(userId: 1);
        $otherUser = new User(userId: 2);

        // Test that both 'read' and 'download' actions work with the same policy method for the author
        $readResultAuthor = $accessControl->isGranted('read', $document, $author);
        $downloadResultAuthor = $accessControl->isGranted('download', $document, $author);

        $this->assertTrue($readResultAuthor->granted);
        $this->assertTrue($downloadResultAuthor->granted);

        // Test that both actions are denied for a different user
        $readResultOther = $accessControl->isGranted('read', $document, $otherUser);
        $downloadResultOther = $accessControl->isGranted('download', $document, $otherUser);

        $this->assertFalse($readResultOther->granted);
        $this->assertFalse($downloadResultOther->granted);
    }

    #[Test]
    public function throws_exception_when_policy_resource_parameter_type_is_invalid(): void
    {
        $this->registerPoliciesFrom(InvalidResourceTypePolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 1);

        $this->expectException(PolicyMethodIsInvalid::class);
        $this->expectExceptionMessageMatches('/The type of the resource parameter of the `.*::invalidResourceType` policy does not match the expected type `.*User`/');

        $accessControl->isGranted('view', $post, $user);
    }

    #[Test]
    public function throws_exception_when_policy_subject_parameter_type_is_invalid(): void
    {
        $this->registerPoliciesFrom(InvalidSubjectTypePolicy::class);

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $user = new User(userId: 1);

        $this->expectException(PolicyMethodIsInvalid::class);
        $this->expectExceptionMessageMatches('/The type of the subject parameter of the `.*::invalidSubjectType` policy does not match the expected type `.*Document`/');

        $accessControl->isGranted('edit', $post, $user);
    }

    #[Test]
    public function policy_for_infers_action_based_on_method_name(): void
    {
        $this->registerPoliciesFrom(PolicyWithoutActionNames::class);

        $accessControl = $this->container->get(AccessControl::class);
        $post = new Post(title: 'Test post', authorId: 1);
        $author = new User(userId: 1);
        $otherUser = new User(userId: 2);

        $resultAuthor = $accessControl->isGranted('can-mark-as-published', $post, $author);
        $resultOther = $accessControl->isGranted('can-mark-as-published', $post, $otherUser);

        $this->assertTrue($resultAuthor->granted);
        $this->assertFalse($resultOther->granted);

        $resultAuthorApprove = $accessControl->isGranted('approve-for-publication', $post, $author);
        $resultOtherApprove = $accessControl->isGranted('approve-for-publication', $post, $otherUser);

        $this->assertTrue($resultAuthorApprove->granted);
        $this->assertFalse($resultOtherApprove->granted);
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

final class PostPolicy
{
    #[PolicyFor(Post::class, action: 'view')]
    public function view(): bool
    {
        return true;
    }

    #[PolicyFor(Post::class, action: PostAction::VIEW)]
    public function viewEnum(): bool
    {
        return true;
    }

    #[PolicyFor(Post::class, action: 'edit')]
    public function edit(?Post $resource, ?User $subject): bool
    {
        if (! ($subject instanceof User)) {
            return false;
        }

        return $resource?->authorId === $subject->id->value;
    }

    #[PolicyFor(Post::class, action: PostAction::EDIT)]
    public function editEnum(?Post $resource, ?User $subject): bool
    {
        if (! ($subject instanceof User)) {
            return false;
        }

        return $resource?->authorId === $subject->id->value;
    }

    #[PolicyFor(Post::class, action: 'delete')]
    public function delete(?Post $resource, ?User $subject): bool|AccessDecision
    {
        if (! ($subject instanceof User)) {
            return false;
        }

        return $resource?->authorId === $subject->id->value
            ? AccessDecision::granted()
            : AccessDecision::denied('Only the author can delete their post');
    }

    #[PolicyFor(Post::class, action: PostAction::DELETE)]
    public function deleteEnum(?Post $resource, ?User $subject): bool|AccessDecision
    {
        if (! ($subject instanceof User)) {
            return false;
        }

        return $resource?->authorId === $subject->id->value
            ? AccessDecision::granted()
            : AccessDecision::denied('Only the author can delete their post');
    }

    #[PolicyFor(Post::class, action: 'create')]
    public function create(): bool
    {
        return true;
    }
}

final class UserPolicy
{
    #[PolicyFor(User::class, action: 'manage')]
    public function manage(?User $resource, ?User $subject): bool
    {
        if (! ($subject instanceof User)) {
            return false;
        }

        return $resource?->id->value === $subject->id->value;
    }
}

final class Document
{
    public PrimaryKey $id;

    public function __construct(
        public string $title,
        public int $authorId,
    ) {}
}

final class MultiActionPolicy
{
    #[PolicyFor(Document::class, action: ['read', 'download'])]
    public function readAndDownload(?Document $resource, ?User $subject): bool
    {
        if (! ($subject instanceof User)) {
            return false;
        }

        return $resource?->authorId === $subject->id->value;
    }
}

final class InvalidResourceTypePolicy
{
    // expects a User as resource but will receive a Post
    #[PolicyFor(Post::class, action: 'view')]
    public function invalidResourceType(User $_resource, ?User $_subject): bool
    {
        return true;
    }
}

final class InvalidSubjectTypePolicy
{
    // expects a Document as subject but will receive a User
    #[PolicyFor(Post::class, action: 'edit')]
    public function invalidSubjectType(?Post $_resource, Document $_subject): bool
    {
        return true;
    }
}

final class PolicyWithoutActionNames
{
    #[PolicyFor(Post::class)]
    public function canMarkAsPublished(?Post $post, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $post?->authorId === $user->id->value;
    }

    #[PolicyFor(Post::class)]
    public function approveForPublication(?Post $post, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $post?->authorId === $user->id->value;
    }
}
