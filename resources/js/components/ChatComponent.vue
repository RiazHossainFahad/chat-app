<template>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Messages</div>

                <div class="card-body">
                    <ul class="list-unstyled" style="height: 300px; overflow-y: scroll" v-chat-scroll>
                        <span class="p-0" v-for="(message, index) in messages" :key="index">
                        <li :style="message.user.id === user.id?'text-align: right; padding-right: 10px' : 'text-align: left'"><strong>{{ message.user.name }}</strong>
                        {{ message.message }}</li>
                        </span>
                    </ul>
                </div>

                <input
                    @keyup.enter = "sendMessage"
                    @keydown="typingEvent"
                    v-model="newMessage"
                    type="text"
                    name="message" 
                    id="message"
                    placeholder="Enter your message here..."
                    class="form-control"
                >
            </div>
            <span class="text-muted" v-if="activeUser"> {{ activeUser.name}} is typing...</span>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Active Users</div>

                <div class="card-body">
                    <ul class="active-user">
                        <li class="py-2" v-for="(user, index) in users" :key="index">{{ user.name }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['user'],
        data() {
            return {
                messages: [],
                newMessage: '',
                users: [],
                activeUser: false,
                timeEvent: false
            }
        },
        created() {
            this.fetchMessages();

            Echo.join('chat')
                .here(user => {
                    this.users = user;
                })
                .joining(user => {
                    this.users.push(user);
                })
                .leaving(user => {
                   this.users = this.users.filter(item => item.id !== user.id);
                })
                .listen('MessageEvent', (event) => {
                    this.messages.push(event.message);
                })
                .listenForWhisper('typing', res => {
                    this.activeUser = res;

                    if(this.timeEvent){
                        clearTimeout(this.timeEvent);
                    }

                    this.timeEvent = setTimeout(() => {
                        this.activeUser = false;
                    }, 3000);
                });
        },
        methods: {
            fetchMessages(){
                axios.get('messages').then(res => {
                    this.messages = res.data;
                })
            },
            sendMessage(){

                this.messages.push({
                    user: this.user,
                    message: this.newMessage
                })

                axios.post('messages', {message: this.newMessage});

                this.newMessage = '';
            },
            typingEvent(){
                Echo.join('chat')
                    .whisper('typing', this.user);
            }
        }
    }
</script>

<style scoped>
ul {
  list-style: none;
}

.active-user li::before {
  content: "\2022";
  color: orangered;
  font-weight: bold;
  display: inline-block; 
  width: 1em;
  margin-left: -1em;
}
</style>
