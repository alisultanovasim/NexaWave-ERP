pipeline {
  agent {
    docker {
      image 'ismatbabir/laravel-jenkins:latest'
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
         sh "mkdir ~/.ssh"
         sh 'echo "$SSH_PRIVATE_KEY_FILE" > ~/.ssh/id_rsa'
         sh "chmod 700 ~/.ssh"

       }
        // sh 'find . -type f -not -path "./vendor/*" -exec chmod 664 {};
        //     find . -type d -not -path "./vendor/*" -exec chmod 775 {} ;'
        sh 'php artisan deploy 213.136.78.83 -s upload'

      }
    }
  }
}
