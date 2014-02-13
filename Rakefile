# Copyright Shareaholic, Inc. (www.shareaholic.com).  All Rights Reserved.

desc 'Get plugin ready for Drupal directory'
task :makerelease do
    puts 'This does nothing yet'
end

task :makequickcopy, :path do |task, args|
  sh "cp -R ../shareaholic_for_drupal #{args[:path]}"
  sh "rm -rf #{args[:path]}/shareaholic_for_drupal/.git"
  sh "sed -i.bak '1,/spreadaholic.com:8080/s/spreadaholic.com:8080/stageaholic.com/' #{args[:path]}/shareaholic_for_drupal/utilities.php"
  sh "sed -i.bak '1,/spreadaholic.com:8080/s/spreadaholic.com:8080/stageaholic.com/' #{args[:path]}/shareaholic_for_drupal/utilities.php"
  sh "sed -i.bak '1,/http/s/http/https/' #{args[:path]}/shareaholic_for_drupal/utilities.php"
  sh "rm #{args[:path]}/shareaholic_for_drupal/utilities.php.bak"
  sh "awk '{if(/http/){count++; if(count==2){gsub(\"http\", \"https\");}} print}' #{args[:path]}/shareaholic_for_drupal/utilities.php > #{args[:path]}/shareaholic_for_drupal/utilities.php.tmp && mv #{args[:path]}/shareaholic_for_drupal/utilities.php.tmp #{args[:path]}/shareaholic_for_drupal/utilities.php"
end

