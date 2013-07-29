function pg_connection_string()
{
  return "dbname=d3bguqragsfthq host=ec2-54-227-238-21.compute-1.amazonaws.com port=5432 user=kfoprjmilqbsgn password=vEatx3mI76WV_0V857JsG10HOS sslmode=require"
}

function pg_connection(){
  # Establish db connection
  $db = pg_connect(pg_connection_string());
  if (!$db) {
     echo "Database connection error."
     exit;
  }
   
 // $result = pg_query($db, "SELECT statement goes here");
  return $db;
}
