/* groovylint-disable CompileStatic, SpaceAroundMapEntryColon */
pipeline {
  agent {
    docker {
      image 'ismatbabir/laravel-jenkins:latest'
      args '-u root'
    }
  }
  stages {
    stage('build') {
      steps {
        sh 'composer install --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts'
        sh 'cp .env.example .env'
      }
    }

    stage('Iint ssh') {
      steps {
        withCredentials(bindings: [
                      file(credentialsId: 'jenkins_deploy_private_key', variable: 'PRIVATE_KEY'),
                      file(credentialsId: 'jenkins_deploy_public_key', variable: 'PUBLIC_KEY')
           ]) {
          sh 'eval "$(ssh-agent -s)"'
          sh 'mkdir -p ~/.ssh'
          sh 'cp \$PRIVATE_KEY ~/.ssh/id_rsa'
          sh 'chmod 600 ~/.ssh/id_rsa'
          sh 'chmod 700 ~/.ssh'
           }
      }
    }

    stage('Deploy To Production') {
      when  {
        branch 'master'
      }
      steps {
        sh 'ssh-keyscan 213.136.78.83 >> ~/.ssh/known_hosts'
        sh 'php artisan deploy 213.136.78.83 -s upload'
      }
    }

    stage('Deploy To Development') {
      when  {
        branch 'development'
      }
      steps {
        sh 'ssh-keyscan time-vps1.serverxx.com >> ~/.ssh/known_hosts'
        sh 'php artisan deploy time-vps1.serverxx.com -s upload'
      }
    }

    // stage('Deploy To Pre-Production') {
    //   when  {
    //     branch 'pre-production'
    //   }
    //   steps {
    //     sh 'ssh-keyscan 213.136.78.83 >> ~/.ssh/known_hosts'
    //     sh 'php artisan deploy 213.136.78.83 -s upload'
    //   }
    // }
  }
}
