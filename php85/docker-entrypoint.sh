#!/bin/bash

# Trigger an error if non-zero exit code is encountered
set -e

eval "$(ssh-agent -s)"
ssh-add /.ssh/*

exec ${@}
