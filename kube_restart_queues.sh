
echo "Starting run all"
date

for podname in $(kubectl get pod | grep trustbp | awk '{print $1}'); do
  echo "Running in ${podname}"
  kubectl exec $podname -- /var/www/html/queue_restarter.sh
done

echo "Run all completed"
date
