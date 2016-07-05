@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h2>What is Fodor?</h2>
            <hr/>
            Auto setup and provision GitHub repositories on a new DigitalOcean droplet
            <img src="/images/install-shield.svg"/>

            <h3>Example repositories</h3>
            <table class="table table-striped table-condensed">
                <tr>
                    <td><a href="{{ url('/provision/fodorxyz/owncloud') }}">fodorxyz/owncloud</a></td><td>Latest OwnCloud personal cloud system</td>
                </tr>
                <tr>
                    <td><a href="{{ url('/provision/fodorxyz/gitlab-ci-multi-runner') }}">fodorxyz/gitlab-ci-multi-runner</a></td><td>Setup a GitLab CI Runner</td>
                </tr>
                <tr>
                    <td><a href="{{ url('/provision/ashleyhindle/fodor-graylog2') }}">ashleyhindle/fodor-graylog2</a></td><td>Graylog central logging system</td>
                </tr>
                <tr>
                    <td><a href="{{ url('/provision/fodorxyz/pritunl') }}">fodorxyz/pritunl</a></td><td>Enterprise vpn server based on OpenVPN</td>
                </tr>
            </table>

            <h2>How does it work?</h2>
            <ol>
                <li>Using the DigitalOcean API we setup a new droplet with a new secure SSH key</li>
                <li>Our systems SSH in to your server and automatically provision based on the provided provisioner script</li>
                <li>Once provisioning is complete, we delete our SSH key from your account</li>
            </ol>

            We then provide you with a Fodor subdomain pointing to the Droplet, and all needed links/information to get started with your new system.

            <h2>Environment Variables</h2>
            Fodor provides the following environment variables to your provisioner script, as well as the input environment variables:
            <table class="table table-condensed">
                <thead>
                <tr>
                    <td>
                        Variable
                    </td>
                    <td>
                        Description
                    </td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <code>$INSTALLPATH</code>
                    </td>
                    <td>
                        This is taken from fodor.json
                    </td>
                </tr>
                <tr>
                    <td>
                        <code>$NAME</code>
                    </td>
                    <td>
                        This is taken from fodor.json
                    </td>
                </tr>
                <tr>
                    <td>
                        <code>$GITURL</code>
                    </td>
                    <td>
                        https://github.com/${NAME}.git
                    </td>
                </tr>
                <tr>
                    <td>
                        <code>$DOMAIN</code>
                    </td>
                    <td>
                        cool-cloud-1984.fodor.xyz
                    </td>
                </tr>
                <tr>
                    <td>
                        <code>$IPV4</code>
                    </td>
                    <td>
                        188.166.9.14
                    </td>
                </tr>
                </tbody>
            </table>
            <!--
            <h2>Our base provisioner script</h2>
            This is run on every new Droplet, before the repositories provisioner is run:
            <div class="commented-code">
{{ $baseScript }}
            </div>
            -->
        </div>

        <div class="col-md-6">
            <h2>Publishing Repositories</h2>
            <hr/>

            <ul>
            <li>
                Your provision URL (to share or add to README.md) will be: https://fodor.xyz/provision/<code>username/reponame</code>
            </li>
            <li>
                We currently only support the <code>master</code> branch
            </li>
            </ul>

            Add a <code>fodor.json</code> file to the root of your repo:</li>
            <div class="commented-code">
{
  <span data-toggle="tooltip" data-placement="left" title="This should be your repo name, we'll prepend https://github.com/ to it for cloning">"name": "username/reponame",</span>
  <span data-toggle="tooltip" data-placement="left" title="Where on the server we'll git clone to">"installpath": "/var/www/fodor-example/",</span>
  <span data-toggle="tooltip" data-placement="left" title="Where is your bash provisioner file? Relative to repo root">"provisioner": "provisioner.sh",</span>
  "text": {
    "complete": "User/Pass: fodor/fodor :)"
  },
  <span data-toggle="tooltip" data-placement="left" title="Which links shall we show to the user after successful install?  Replacements: @{{DOMAIN}} => e.g.'clean-clouds-5829.fodor.xyz'">"links": [</span>
    {
      "title": "Documentation",
      <span data-toggle="tooltip" data-placement="left" title="Replacements: @{{DOMAIN}} => e.g.'clean-clouds-5829.fodor.xyz'">"url": "http://@{{DOMAIN}}/"</span>
    }
  ],
  "size": {
    <span data-toggle="tooltip" data-placement="left" title="Optional - which size Droplet is suggested?">"suggested": "512mb",</span>
    <span data-toggle="tooltip" data-placement="left" title="Optional - which size Droplet is required? All lower Droplet sizes will be disabled">"required": "512mb"</span>
  },
  <span data-toggle="tooltip" data-placement="left" title="ubuntu-14-04-x64 and docker are currently the only supported distros">"distro": "ubuntu-14-04-x64",</span>
  <span data-toggle="tooltip" data-placement="left" title="This is shown on the provision page to the user">"description": "Describe what the user gets",</span>
  <span data-toggle="tooltip" data-placement="left" title="For discovery within Fodor.xyz">"keywords": ["fodor", "example", "nginx"],</span>
  "homepage": "https://fodor.xyz",
  <span data-toggle="tooltip" data-placement="left" title="If you need information from users, use these inputs.  They'll be made available as environment variables to your provisioner. e.g. apikey will be available as $APIKEY">"inputs": [</span>
    {
        "title": "API Key",
        <span data-toggle="tooltip" data-placement="left" title="This will be available as $APIKEY to your provisioner script">"name": "apikey",</span>
        "placeholder": "xxxx-xxxx-xxxx-xxxx",
        "type": "regex",
        "regex": "[a-zA-Z\\-0-9]+"
    },
    {
        "title": "Server Type",
        "name": "type",
        "type": "select",
        "options": ["nginx", "apache", "iis", "lighttpd"]
    },
    {
        "title": "Name",
        "name": "name",
        <span data-toggle="tooltip" data-placement="left" title="This will allow empty strings">"type": "string"</span>
    },
    {
        "title": "Email",
        "name": "email",
        "type": "email"
    },
    {
        "title": "Password",
        "name": "password",
        "type": "password"
    },
    {
        "title": "URL",
        "name": "url",
        "type": "url"
    },
    {
        "title": "Age",
        "name": "age",
        <span data-toggle="tooltip" data-placement="left" title="This will allow whole numbers, floats and negative numbers.  The environment variable will be a string - 27='27'">"type": "number"</span>
    }
  ]
}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
