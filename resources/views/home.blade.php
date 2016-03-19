@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h2>What is Fodor?</h2>
            <hr/>
            Auto setup and provision GitHub repositories on a new DigitalOcean droplet

            <h3>Example repositories</h3>
            <ul class="list-unstyled">
                <li>
                    <a href="{{ url('/provision/fodorxyz/owncloud') }}">fodorxyz/owncloud</a> &bull; Latest OwnCloud personal cloud system
                </li>
                <li>
                    <a href="{{ url('/provision/ashleyhindle/fodor-example') }}">ashleyhindle/fodor-example</a> &bull; Simple nginx setup example
                </li>
                <li>
                    <a href="{{ url('/provision/ashleyhindle/fodor-graylog2') }}">ashleyhindle/fodor-graylog2</a> &bull; Graylog central logging system
                </li>
                <li>
                    <a href="{{ url('/provision/fodorxyz/pritunl') }}">fodorxyz/pritunl</a> &bull; Enterprise vpn server based on OpenVPN
                </li>
            </ul>

            <h2>How does it work?</h2>
            <ol>
                <li>Using the DigitalOcean API we setup a new droplet with a new secure SSH key</li>
                <li>Our systems SSH in to your server and automatically provision based on the provided provisioner script</li>
                <li>Once provisioning is complete, we delete our SSH key from your account</li>
            </ol>

            We then provide you with a Fodor subdomain pointing to the Droplet, and all needed links/information to get started with your new system.

            <h2>Our base provisioner script</h2>
            This is run on every new Droplet, before the repositories provisioner is run:
            <div class="commented-code">
#!/bin/bash
export DEBIAN_FRONTEND=noninteractive
set -e # Error out on any errors

echo 'root:@{{ $rootPasswordEscaped }}' \
    | chpasswd

export INSTALLPATH="@{{ $installpath }}"
export NAME="@{{ $name }}"
export GITURL="https://github.com/${NAME}.git"

apt-get -y update
apt-get -y install git

mkdir -p $INSTALLPATH
cd $INSTALLPATH
git clone --depth 1 $GITURL .
            </div>
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
  <span data-toggle="tooltip" data-placement="left" title="Where is your bash provisioner file? Relative to repo root">"provisioner": "fodor/provisioner.sh"</span>
  "text":
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
  <span data-toggle="tooltip" data-placement="left" title="ubuntu-14-04-x64 is currently the only supported distro">"distro": "ubuntu-14-04-x64",</span>
  <span data-toggle="tooltip" data-placement="left" title="This is shown on the provision page to the user">"description": "Describe what the user gets",</span>
  <span data-toggle="tooltip" data-placement="left" title="For discovery within Fodor.xyz">"keywords": ["fodor", "example", "nginx"],</span>
  "homepage": "https://fodor.xyz",
  <span data-toggle="tooltip" data-placement="left" title="If you need information from users, use these inputs.  They'll be made available as environment variables to your provisioner. e.g. fakeapikey will be available as $FAKEAPIKEY">"inputs": [</span>
    {
      "title": "Fake API Key",
      <span data-toggle="tooltip" data-placement="left" title="This will be available as $FAKEAPIKEY to your provisioner script">"name": "fakeapikey",</span>
      "placeholder": "xxxx-xxxx-xxxx-xxxx",
      "type": "regex",
      "regex": "[a-zA-Z\\-0-9]+"
    }
  ]
}
                </div>

            We also provide the following environment variables to your provisioner script:
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
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection
