import {Component, ViewChild} from '@angular/core';
import { RouterOutlet } from '@angular/router';
import {SideNavComponent} from "./components/side-nav/side-nav.component";
import {ListComponent} from "./components/track-list/list.component";
import {DetailsComponent} from "./components/track-details/details.component";
import {MatInputModule} from "@angular/material/input";
import {CardWindowComponent} from "./components/card-window/card-window.component";

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    RouterOutlet,
    SideNavComponent,
    ListComponent,
    DetailsComponent,
    MatInputModule,
    CardWindowComponent,
  ],
  template: `
    <app-side-nav (navItemSelected)="onNavItemSelected($event)" (requestOpenCard)="openCard($event)"></app-side-nav>
    <app-list
        [selectedItem]="selectedNavItem"
        (itemSelected)="onItemSelected($event)"></app-list>
    <app-details [selectedItem]="selectedItem" [selectedNavItem]="selectedNavItem"></app-details>
    <app-card-window #cardWindow></app-card-window>
  `,
  styleUrl: "app.css",
})
export class AppComponent {
  @ViewChild('cardWindow') cardWindow!: CardWindowComponent;

  ngAfterViewInit() {
    // cardWindow тепер визначено
  }

  openCard(content: string) {
    if (this.cardWindow) {
      this.cardWindow.openCard();
      this.cardWindow.setContent(content);
    }
  }

  title = 'BohdansStorage';
  selectedNavItem: string = 'storage';

  selectedItem: any;

  ngOnInit() {
    const savedItem = localStorage.getItem('activeNavItem');
    if (savedItem) {
      this.selectedNavItem = savedItem;
    }
  }

  onNavItemSelected(item: string) {
    this.selectedNavItem = item;
  }

  onItemSelected(item: any) {
    this.selectedItem = item;
  }
}
