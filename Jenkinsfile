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
       withCredentials(bindings: [
       file(credentialsId: 'jenkins_deploy_private_key',variable: 'PRIVATE_KEY'),
       file(credentialsId: 'jenkins_deploy_public_key',variable: 'PUBLIC_KEY')
       ]) {
//          sh 'eval "$(ssh-agent -s)"'
         sh "mkdir -p ~/.ssh"
         sh 'cp \$PRIVATE_KEY ~/.ssh/id_rsa'
//          sh 'cp \$PUBLIC_KEY ~/.ssh/id_rsa.pub'
         sh "chmod 600 ~/.ssh/id_rsa"
         sh "ssh-keyscan 213.136.78.83 >> ~/.ssh/known_hosts"
         sh 'ssh -o StrictHostKeyChecking=no developer@213.136.78.83 "whoami"'
//          sh "echo -e "StrictHostKeyChecking no" > ~/.ssh/config"
         sh 'php artisan deploy 213.136.78.83 -s upload'
       }
//         sh 'find . -type f -not -path "./vendor/*" -exec chmod 664 {};'
//         sh 'find . -type d -not -path "./vendor/*" -exec chmod 775 {} ;'

      }
    }
  }
}
