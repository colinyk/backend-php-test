let todo = `
<div>
    <table class="table table-striped ">
        <th>#</th>
        <th>User</th>
        <th>Description</th>
        <th class='text-center'>As JSON</th>
        <th class='text-center'>Completed</th>
        <th class='text-center'>Delete</th>
        <tbody>
            <tr v-for="(todo) in todos">
                <td>{{ todo.id }}</td>
                <td>{{ todo.user_id }}</td>
                <td>
                    {{ todo.description }}
                </td>
                <td class='text-center'>
                    &nbsp;
                </td>
                <td class="text-center">
                     <span v-if='todo.completed=="1"'>Yes</span>
                     <span v-else>
                        <button class="btn btn-xs btn-success" v-on:click.prevent='complete(todo)'>No</button>
                     </span>
                </td>
                <td class='text-center'>
                    <button class="btn btn-xs btn-danger" v-on:click.prevent='deleting(todo)'>
                        <span class="glyphicon glyphicon-remove glyphicon-white"></span>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
    <div class='alert alert-success' v-if='message'>{{ message }}</div>
</div>
`;


/**
 * colinyk/backend-php-test
 */
export default {
  template: todo,
  data() {
    return { 
        todos: [], 
        errors: [], 
        message: null
    }
  },
  mounted() {
    //onload event
    this.title = document.title = this.$route.meta.title
    this.loadData();
  },
  methods: {
      /**
       * load todo list
       */
      loadData: function(){
        let self = this  
        const uri = '/todo?type=json'
        this.$http.get(uri)
          .then(response => {
              self.todos = response.data
          })
          .catch(e => {
              self.errors.push(e)
          })
      },
      /**
       * set todo completed
       */
      complete: function(todo){
        if (! confirm('Are you sure you want to set this item as completed? ')){
            return false;
        }
        
        let self = this  
        const uri = `/todo/completed/${todo.id}?type=json`
        this.$http.post(uri, this.data)
        .then(function(response){
            if (response.data.error !== undefined){
                // POST error 
                self.errors.push(response.data.error);
            }else{
                self.loadData();
            }
        })
        .catch(function(error){
            self.errors.push(error);
        });          
      },
      /**
       * delete selected todo 
       */
      deleting: function(todo){
        if (! confirm('Are you sure you want to delete this item? ')){
            return false;
        }
        
        let self = this  
        const uri = `/todo/delete/${todo.id}?type=json`
        this.$http.post(uri, this.data)
        .then(function(response){
            if (response.data.error !== undefined){
                // POST error 
                self.errors.push(response.data.error);
            }else{
                self.loadData();
            }
        })
        .catch(function(error){
            self.errors.push(error);
        });          
      },
      
  }
};

