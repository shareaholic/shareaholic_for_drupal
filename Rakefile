# Copyright Shareaholic, Inc. (www.shareaholic.com).  All Rights Reserved.

desc 'Get plugin ready for Drupal directory'
task :makerelease, :path do |task, args|
  sh "rsync -av --exclude='.*' ./ #{args[:path]}"
  sh "sed -i.bak '1,/spreadaholic.com:8080/s/spreadaholic.com:8080/shareaholic.com/' #{args[:path]}/utilities.php"
  sh "sed -i.bak '1,/spreadaholic.com:8080/s/spreadaholic.com:8080/web.shareaholic.com/' #{args[:path]}/utilities.php"
  sh "sed -i.bak '1,/recommendations.stageaholic.com/s/recommendations.stageaholic.com/recommendations.shareaholic.com/' #{args[:path]}/utilities.php"
  sh "rm #{args[:path]}/utilities.php.bak"
  sh "awk '{if(/http/){count++; if(count>=1 && count<=2){gsub(\"http\", \"https\");}} print}' #{args[:path]}/utilities.php > #{args[:path]}/utilities.php.tmp && mv #{args[:path]}/utilities.php.tmp #{args[:path]}/utilities.php"
  sh "rm -rf #{args[:path]}/tests"
  sh "rm -rf #{args[:path]}/Rakefile"
end

task :makequickcopy, :path do |task, args|
  sh "rsync -av --exclude='.*' ./ #{args[:path]}"
  sh "sed -i.bak '1,/spreadaholic.com:8080/s/spreadaholic.com:8080/stageaholic.com/' #{args[:path]}/utilities.php"
  sh "sed -i.bak '1,/spreadaholic.com:8080/s/spreadaholic.com:8080/stageaholic.com/' #{args[:path]}/utilities.php"
  sh "rm #{args[:path]}/utilities.php.bak"
  sh "awk '{if(/http/){count++; if(count>=1 && count<=2){gsub(\"http\", \"https\");}} print}' #{args[:path]}/utilities.php > #{args[:path]}/utilities.php.tmp && mv #{args[:path]}/utilities.php.tmp #{args[:path]}/utilities.php"
end

