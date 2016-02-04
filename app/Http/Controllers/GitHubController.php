<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Socialite;


class GitHubController extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function start()
    {
        return Socialite::driver('github')
            ->scopes(['user:email'])->redirect(); // Don't need scopes, we only want public read access to get GitHub repos
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function callback(Request $request)
    {
        $user = Socialite::with('github')->user(); // TODO: Nice error page

        $request->session()->set('github', [ // TODO: Improve storage
            'token' => $user->token,
            'id' => $user->getId(),
            'nickname' => $user->getNickname(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar()
        ]);

        $client = new \Github\Client();
        $client->authenticate($user->token, false, \Github\Client::AUTH_HTTP_TOKEN);
        //$repos = $client->api('user')->repositories($user->getNickname()); // Get this users repos
        $content = $client->api('repo')->contents()->show('ashleyhindle', 'fodor-example', 'fodor.json', 'master');
        dd(base64_decode($content['content']));
    }
}
