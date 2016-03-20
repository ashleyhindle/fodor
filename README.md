# Fodor &bull; [![travis-build-status](https://travis-ci.org/fodorxyz/fodor.svg)](https://travis-ci.org/fodorxyz/fodor)
Auto setup and provision GitHub repositories on a new DigitalOcean droplet

# Use the site  
[https://fodor.xyz](https://fodor.xyz)

# Make your repo Fodor friendly  
Simply add a `fodor.json` file to the root of your repo, and a bash provisioner somewhere.  You can see an example `fodor.json` file here: https://fodor.xyz/provision/ashleyhindle/fodor-example

# Todo

* Refactor - figure out the best way to use facades and service providers so Cloudflare, DigitalOcean API/OAuth and GitHub API/OAuth are all fancily reflected in
