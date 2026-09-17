pipeline {
    agent any

    environment {
        DEPLOY_PATH = '/var/www/gamestore-cameroun'
        BRANCH_PROD = 'main'
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'git@github.com:tonuser/gamestore-cameroun.git', credentialsId: 'github-ssh-key'
            }
        }

        stage('Install Backend Deps') {
            steps {
                sh 'composer install --no-dev --optimize-autoloader'
            }
        }

        stage('Install Frontend Deps (Nuxt)') {
            steps {
                dir('frontend') {
                    sh 'npm ci'
                }
            }
        }

        stage('Run Tests') {
            steps {
                sh 'cp .env.testing .env'
                sh 'php artisan key:generate'
                sh 'php artisan test'
            }
        }

        stage('Build Frontend') {
            steps {
                dir('frontend') {
                    sh 'npm run build'
                }
            }
        }

        stage('Deploy') {
            when { branch 'main' }
            steps {
                sshagent(['deploy-server-key']) {
                    sh """
                        rsync -avz --exclude='.git' --exclude='node_modules' \
                        ./ user@ton-serveur:${DEPLOY_PATH}

                        ssh user@ton-serveur '
                            cd ${DEPLOY_PATH} &&
                            composer install --no-dev --optimize-autoloader &&
                            php artisan migrate --force &&
                            php artisan config:cache &&
                            php artisan route:cache &&
                            php artisan view:cache &&
                            sudo systemctl reload php8.2-fpm &&
                            sudo systemctl reload nginx
                        '
                    """
                }
            }
        }
    }

    post {
        success {
            echo '✅ Déploiement réussi !'
        }
        failure {
            echo '❌ Le pipeline a échoué.'
        }
    }
}