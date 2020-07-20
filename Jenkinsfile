pipeline {
  agent {
    docker {
      image 'ismatbabir/laravel-jenkins:latest'
      args "-e HOME=${JENKINS_HOME} -u root"

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
       withCredentials(bindings: [file(credentialsId: 'jenkins_devloy_private_key',variable: 'PRIVATE_KEY'),]) {
         sh "mkdir -p ~/.ssh"
         sh 'cp \$PRIVATE_KEY ~/.ssh/id_rsa'
         sh "chmod 600 ~/.ssh/id_rsa"
         sh "ssh-keyscan 213.136.78.83 >> ~/.ssh/known_hosts"
         sh 'ssh developer@213.136.78.83 "whoami" -vvv'
         sh 'php artisan deploy 213.136.78.83 -s upload'
       }
//         sh 'find . -type f -not -path "./vendor/*" -exec chmod 664 {};'
//         sh 'find . -type d -not -path "./vendor/*" -exec chmod 775 {} ;'

      }
    }
  }
}
