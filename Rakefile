# Copyright Shareaholic, Inc. (www.shareaholic.com).  All Rights Reserved.

desc 'Get plugin ready for Drupal directory'
task :makerelease do
    puts 'This does nothing yet'
end

task :makequickcopy, :path do |task, args|
  sh "cp -R ../shareaholic_for_drupal #{args[:path]}"
  sh "rm -rf #{args[:path]}/shareaholic_for_drupal/.git"
  sh "sed -i.bak 's/spreadaholic.com:8080/stageaholic.com/' #{args[:path]}/shareaholic_for_drupal/shareaholic.module"
  sh "sed -i.bak 's/http/https/' #{args[:path]}/shareaholic_for_drupal/shareaholic.module"
  sh "rm #{args[:path]}/shareaholic_for_drupal/shareaholic.module.bak"
end

