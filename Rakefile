# Copyright Shareaholic, Inc. (www.shareaholic.com).  All Rights Reserved.

desc 'Get plugin ready for Drupal directory'
task :makerelease do
    puts 'This does nothing yet'
end

task :makequickcopy, :path do |task, args|
  sh "cp -R ../shareaholic_for_drupal #{args[:path]}"
  sh "rm -rf #{args[:path]}/shareaholic_for_drupal/.git"
end

