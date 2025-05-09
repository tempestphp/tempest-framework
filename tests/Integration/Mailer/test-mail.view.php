<x-mail
        from="brent@tempestphp.com"
        :to="$user->email"
        :subject="'Welcome ' . $user->name"
        :attachments="$files"
>
    Hello {{ $user->first_name }}

    Thanks for checking out Tempest!

    See you soon!

    — The Tempest Team
</x-mail>