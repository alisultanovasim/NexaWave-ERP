pipeline {
  agent {
    docker {
      image 'ismatbabir/laravel-jenkins:latest'
      args "-e HOME=${JENKINS_HOME} -u root"

    }
  }
  stages {
    // when {
    //        branch 'development'
    //      }
    stage('build') {
      steps {
        sh 'composer install --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts'
        sh 'cp .env.example .env'
      }
    }
    stage('Deploy') {
      steps {
       withCredentials(bindings: [sshUserPrivateKey(credentialsId: 'jenkins_private_key',
                                                    keyFileVariable: 'SSH_PRIVATE_KEY_FILE',
                                                    passphraseVariable: '',
                                                    usernameVariable: 'USERNAME')]) {
         sh 'eval "$(ssh-agent -s)"'
         sh "mkdir -p ~/.ssh"
         sh 'echo "$SSH_PRIVATE_KEY_FILE" > ~/.ssh/id_rsa'
         sh "chmod 700 ~/.ssh"
         sh "chmod 600 ~/.ssh/id_rsa"
         sh "ssh-keyscan github.com >> ~/.ssh/known_hosts"
         sh "ssh-keyscan 213.136.78.83 >> ~/.ssh/known_hosts"

       }
//         sh 'find . -type f -not -path "./vendor/*" -exec chmod 664 {};'
//         sh 'find . -type d -not -path "./vendor/*" -exec chmod 775 {} ;'
        sh 'php artisan deploy 213.136.78.83 -s upload'

      }
    }
  }
}
