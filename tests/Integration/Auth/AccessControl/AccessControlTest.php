<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\AccessControl;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AccessControl\AccessControl;
use Tempest\Auth\AccessControl\AccessDecision;
use Tempest\Auth\AccessControl\Policy;
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Authentication\AuthenticatorInitializer;
use Tempest\Database\PrimaryKey;
use Tests\Tempest\Integration\Auth\Fixtures\InMemoryAuthenticatorInitializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class AccessControlTest extends FrameworkIntegrationTestCase
{
    use HasPolicyTests;

    #[Test]
    public function complete_access_control_workflow(): void
    {
        $this->registerPoliciesFrom(ArticlePolicy::class);
        $this->registerPoliciesFrom(CommentPolicy::class);

        $admin = new TestUser(userId: 1, role: 'admin');
        $author = new TestUser(userId: 2, role: 'author');
        $viewer = new TestUser(userId: 3, role: 'viewer');
        $article = new Article(title: 'Test Article', authorId: 2);
        $comment = new ArticleComment(content: 'Test Comment', authorId: 3, articleId: 1);

        $accessControl = $this->container->get(AccessControl::class);

        // admin
        $this->assertTrue($accessControl->isGranted('manage', $article, subject: $admin)->granted);
        $this->assertTrue($accessControl->isGranted('delete', $comment, subject: $admin)->granted);

        // author
        $this->assertTrue($accessControl->isGranted('edit', $article, subject: $author)->granted);
        $this->assertFalse($accessControl->isGranted('edit', $comment, subject: $author)->granted);

        // viewer
        $this->assertTrue($accessControl->isGranted('view', $article, subject: $viewer)->granted);
        $this->assertFalse($accessControl->isGranted('edit', $article, subject: $viewer)->granted);

        // unauthenticated
        $this->assertTrue($accessControl->isGranted('view', $article)->granted);
        $this->assertFalse($accessControl->isGranted('edit', $article)->granted);
    }

    #[Test]
    public function authentication_integration(): void
    {
        $this->registerPoliciesFrom(ArticlePolicy::class);

        $this->container->removeInitializer(AuthenticatorInitializer::class);
        $this->container->addInitializer(InMemoryAuthenticatorInitializer::class);

        $author = new TestUser(userId: 2, role: 'author');
        $article = new Article(title: 'Test Article', authorId: 2);

        $accessControl = $this->container->get(AccessControl::class);

        // Without authentication
        $this->assertFalse($accessControl->isGranted('edit', $article)->granted);

        // Authenticate
        $authenticator = $this->container->get(Authenticator::class);
        $authenticator->authenticate($author);

        // Should use authenticated user automatically
        $this->assertTrue($accessControl->isGranted('edit', $article)->granted);
        $this->assertTrue($accessControl->isGranted('delete', $article)->granted);

        // Deauthenticate
        $authenticator->deauthenticate();

        // Should deny access now
        $this->assertFalse($accessControl->isGranted('edit', $article)->granted);
    }
}

enum ArticleAction: string
{
    case VIEW = 'view';
    case EDIT = 'edit';
    case DELETE = 'delete';
    case MANAGE = 'manage';
}

final class TestUser implements Authenticatable
{
    public PrimaryKey $id;

    public function __construct(
        int $userId,
        public string $role,
    ) {
        $this->id = new PrimaryKey($userId);
    }
}

final class Article
{
    public PrimaryKey $id;

    public function __construct(
        public string $title,
        public int $authorId,
    ) {}
}

final class ArticleComment
{
    public PrimaryKey $id;

    public function __construct(
        public string $content,
        public int $authorId,
        public int $articleId,
    ) {}
}

final class ArticlePolicy
{
    #[Policy(Article::class, action: 'view')]
    public function view(): bool
    {
        return true;
    }

    #[Policy(Article::class, action: 'edit')]
    public function edit(Article $article, ?TestUser $subject): bool
    {
        if ($subject === null) {
            return false;
        }

        return $subject->role === 'admin' || $subject->role === 'author' && $article->authorId === $subject->id->value;
    }

    #[Policy(Article::class, action: 'delete')]
    public function delete(Article $article, ?TestUser $subject): bool|AccessDecision
    {
        if ($subject === null) {
            return AccessDecision::denied('Authentication required');
        }

        if ($subject->role === 'admin') {
            return true;
        }

        if ($subject->role === 'author' && $article->authorId === $subject->id->value) {
            return true;
        }

        return AccessDecision::denied('Insufficient permissions for this action');
    }

    #[Policy(Article::class, action: 'manage')]
    public function manage(?Article $_, ?TestUser $subject): bool
    {
        return $subject?->role === 'admin';
    }
}

final class CommentPolicy
{
    #[Policy(ArticleComment::class, action: 'view')]
    public function view(): bool
    {
        return true;
    }

    #[Policy(ArticleComment::class, action: 'edit')]
    public function edit(ArticleComment $comment, ?TestUser $subject): bool
    {
        if ($subject === null) {
            return false;
        }

        return $comment->authorId === $subject->id->value || $subject->role === 'admin';
    }

    #[Policy(ArticleComment::class, action: 'delete')]
    public function delete(ArticleComment $_, ?TestUser $subject): bool
    {
        return $subject?->role === 'admin';
    }
}
