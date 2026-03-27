<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\ModelInspector;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsToMany;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

/**
 * @internal
 */
final class SameTableRelationsTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function has_one_two_properties_to_same_table(): void
    {
        $model = inspect(model: PersonWithTwoAddresses::class);

        $home = $model->getRelation(name: 'homeAddress')->setParent(name: '');
        $work = $model->getRelation(name: 'workAddress')->setParent(name: '');

        $homeJoin = $home->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);
        $workJoin = $work->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);

        $this->assertNotEquals($homeJoin, $workJoin);
        $this->assertStringContainsString('addresses AS homeAddress', $homeJoin);
        $this->assertStringContainsString('addresses AS workAddress', $workJoin);
    }

    #[Test]
    public function has_many_two_properties_to_same_table(): void
    {
        $model = inspect(model: UserWithTwoMessageRelations::class);

        $sent = $model->getRelation(name: 'sentMessages')->setParent(name: '');
        $received = $model->getRelation(name: 'receivedMessages')->setParent(name: '');

        $sentJoin = $sent->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);
        $receivedJoin = $received->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);

        $this->assertNotEquals($sentJoin, $receivedJoin);
        $this->assertStringContainsString('messages AS sentMessages', $sentJoin);
        $this->assertStringContainsString('messages AS receivedMessages', $receivedJoin);
    }

    #[Test]
    public function belongs_to_many_two_properties_to_same_table(): void
    {
        $model = inspect(model: UserWithTwoBelongsToManyRelations::class);

        $followers = $model->getRelation(name: 'followers')->setParent(name: '');
        $following = $model->getRelation(name: 'following')->setParent(name: '');

        $followersJoin = $followers->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);
        $followingJoin = $following->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);

        $this->assertNotEquals($followersJoin, $followingJoin);
        $this->assertStringContainsString('AS followers', $followersJoin);
        $this->assertStringContainsString('AS following', $followingJoin);
    }

    #[Test]
    public function child_with_two_has_one_to_same_subchild_table(): void
    {
        $model = inspect(model: PersonWithTwoAddresses::class);

        $home = $model->getRelation(name: 'homeAddress')->setParent(name: 'person');
        $work = $model->getRelation(name: 'workAddress')->setParent(name: 'person');

        $homeJoin = $home->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);
        $workJoin = $work->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);

        $this->assertNotEquals($homeJoin, $workJoin);
        $this->assertStringContainsString('AS person_homeAddress', $homeJoin);
        $this->assertStringContainsString('AS person_workAddress', $workJoin);
    }

    #[Test]
    public function child_with_two_has_many_to_same_subchild_table(): void
    {
        $model = inspect(model: UserWithTwoMessageRelations::class);

        $sent = $model->getRelation(name: 'sentMessages')->setParent(name: 'user');
        $received = $model->getRelation(name: 'receivedMessages')->setParent(name: 'user');

        $sentJoin = $sent->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);
        $receivedJoin = $received->getJoinStatement()->compile(dialect: DatabaseDialect::SQLITE);

        $this->assertNotEquals($sentJoin, $receivedJoin);
        $this->assertStringContainsString('AS user_sentMessages', $sentJoin);
        $this->assertStringContainsString('AS user_receivedMessages', $receivedJoin);
    }
}

#[Table('same_test_persons')]
final class PersonWithTwoAddresses
{
    public PrimaryKey $id;

    #[HasOne(ownerJoin: 'home_person_id')]
    public ?SameTestAddress $homeAddress = null;

    #[HasOne(ownerJoin: 'work_person_id')]
    public ?SameTestAddress $workAddress = null;

    public string $name;
}

#[Table('same_test_addresses')]
final class SameTestAddress
{
    public PrimaryKey $id;

    public ?int $home_person_id = null;

    public ?int $work_person_id = null;

    public string $street;
}

#[Table('same_test_users')]
final class UserWithTwoMessageRelations
{
    public PrimaryKey $id;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\SameTestMessage[] */
    #[HasMany(ownerJoin: 'sender_id')]
    public array $sentMessages = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\SameTestMessage[] */
    #[HasMany(ownerJoin: 'receiver_id')]
    public array $receivedMessages = [];

    public string $name;
}

#[Table('same_test_messages')]
final class SameTestMessage
{
    public PrimaryKey $id;

    public ?int $sender_id = null;

    public ?int $receiver_id = null;

    public string $body;
}

#[Table('same_test_btm_users')]
final class UserWithTwoBelongsToManyRelations
{
    public PrimaryKey $id;

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\SameTestBtmUser[] */
    #[BelongsToMany(pivot: 'followers_pivot')]
    public array $followers = [];

    /** @var \Tests\Tempest\Integration\Database\ModelInspector\SameTestBtmUser[] */
    #[BelongsToMany(pivot: 'following_pivot')]
    public array $following = [];

    public string $name;
}

#[Table('same_test_btm_targets')]
final class SameTestBtmUser
{
    public PrimaryKey $id;

    public string $name;
}
