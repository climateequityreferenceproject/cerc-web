# Overview
This directory contains the Dockerfile used to generate a fully self-contained Docker image of cerc-web and all its dependencies.

# Usage
1. Obtain Docker for your operating system from https://www.docker.com/
* If you use Docker Desktop, you can pull the docker image (climateequityreferenceproject/cerc-web) directly from within Docker Desktop otherwise run on your command line: `docker pull climateequityreferenceproject/cerc-web`.
* Alternatively, you can use the Dockerfile contained in this folder to automatically build the image yourself from source (only the Dockerfile is needed, everything else will be pulled from various sources as needed). Run `docker build -t climateequityreferenceproject/cerc-web .` from the command line within the directory where the Dockerfile is located.
* To start the image from the commandline, type `docker run -it -rm -d --name cerc-web -p 8080:80  climateequityreferenceproject/cerc-web:latest` (if applicable, replace 'latest' with the specific release you're using, for example "version-3.2.1"). If you want you can replace the 8080 in the last comment with a different port number, just make use to replace it in the URL below also.
* In your browser, navigate to `http://localhost:8080/index.php` to start cerc-web.  

## Troubleshooting
It seems that in some cases the automatic startup using the approach above hangs at the starting of the MySQL server. In this case, try this:

    $ docker pull climateequityreferenceproject/cerc-web
    $ docker run -it --rm -d --name cerc-web -p 8080:80 climateequityreferenceproject/cerc-web bash
    # dumb-init /start.sh`

# Finally
This directory also contains install scripts that can be used to automatically install cerc-web into a devilbox Docker container (http://devilbox.org) or into a standard LEMP stack, such as https://hub.docker.com/r/tonisormisson/dev-lemp

The Docker Hub page of cerc-web is at https://hub.docker.com/r/climateequityreferenceproject/cerc-web.
