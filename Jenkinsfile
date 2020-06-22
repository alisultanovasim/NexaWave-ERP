pipeline {
  agent {
    docker {
      image 'lorisleiva/laravel-docker:latest'
    }

  }
  stages {
    stage('build') {
      steps {
        sh 'composer install --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts'
        sh 'cp .env.example .env'
      }
    }

    stage('Deploy') {
      steps {
        sh '''eval $(ssh-agent -s)
  echo "${TEST_SSH_PRIVATE_KEY}" | tr -d \'\\r\' | ssh-add - > /dev/null
  mkdir -p ~/.ssh
  chmod 700 ~/.ssh
  [[ -f /.dockerenv ]] && echo -e "Host *\\n\\tStrictHostKeyChecking no\\n\\n" > ~/.ssh/config'''
        sh '''find . -type f -not -path "./vendor/*" -exec chmod 664 {} \\;
  find . -type d -not -path "./vendor/*" -exec chmod 775 {} \\;'''
        sh 'php artisan deploy 213.136.78.83 -s upload'
      }
    }

  }
  environment {
    TEST_SSH_PRIVATE_KEY = '-----BEGIN RSA PRIVATE KEY----- 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 -----END RSA PRIVATE KEY-----'
  }
}