import Tool from './pages/Tool'
import NotificationCard from './components/NotificationCard'

Nova.booting((app/*, store*/) => {
  Nova.inertia('NotificationCenter', Tool)
  app.component('NotificationCard', NotificationCard)
})
