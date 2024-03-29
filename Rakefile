ENV['DUID'] = Process.uid.to_s
ENV['DGID'] = Process.gid.to_s
DOCKERCOMPOSE='docker-compose.dev.yml'

$alias_container = {
      'main' => 'php',
}

def compose(*args)
  sh "docker-compose -f #{DOCKERCOMPOSE} #{args.join(' ')}"
end

namespace :dev do
  desc 'up environment'
  task :up do
    compose 'up', '-d --build'
  end

  desc 'initialize environment'
  task :init do
    compose 'exec', $alias_container['main'], 'composer install'
  end

  desc 'down environment'
  task :down do
    compose 'down', '-v'
  end

  desc 'build'
  task :build, :container do |_, args|
    container = $alias_container.fetch(args.container, args.container)

    compose "build #{container}"
  end

  desc "sh <container>"
  task :sh, :container do |_, args|
    container = $alias_container.fetch(args.container, args.container)
    compose 'exec', container, 'bash'
  end

  desc "restart <container>"
  task :restart, :container do |_, args|
    container = $alias_container.fetch(args.container, args.container)
    compose 'restart', container
  end

  desc "logs -f <container>"
  task :tail, :container do |_, args|
    container = $alias_container.fetch(args.container, args.container)
    compose 'logs', '-f', container
  end

  task :sql, :database do |_, args|
    compose 'exec', 'db psql -U postgres', args.database
  end
end

desc 'tdd'
task :tdd do
  compose 'exec', $alias_container['main'], 'bash -c "composer run commit"'
end
